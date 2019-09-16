<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Agent;
use App\Models\Provider;
use App\Models\Wager;
use App\Models\WagerIfun;
use App\Models\WalletGame;
use App\Models\Slot;
use App\Models\Transfer;
use App\Models\Ledger;
use App\Models\LedgerExternal;
use App\Models\QueryLog;
use Illuminate\Http\Request;
use App\Services\HelperService;
use Auth;
use DB;
use Curl\Curl;

class ReportController extends Controller
{
    // ENABLE / DISABLE QUERY LOG
    const ENABLE_QUERY_LOG = true;
    /**
     * @var HelperService
     */
    private $helper;

    public function __construct(HelperService $helper)
    {
        if (self::ENABLE_QUERY_LOG) {
            DB::enableQueryLog();
        }
        $this->helper = $helper;
    }

    protected function logQuery($catalog = 'none')
    {
        if (self::ENABLE_QUERY_LOG) {
            $qlogs = DB::getQueryLog();
            foreach ($qlogs as $l) {
                $str = $l['query'] . "\n";
                if (count($l['bindings'])) {
                    $str .= "binds:\n" . print_r($l['bindings'], true);
                }
                $str .= 'takes:' . $l['time'];

                $log = new QueryLog;
                $log->operator = Auth::user()->id;
                $log->catalog = $catalog;
                $log->log = $str;
                $log->save();
            }
        }
    }

    public function getIdsByTimeRange($table, $id_column, $time_column, $from = '', $to = '')
    {
        if (!$from) {
            $from = date('Y-m-d');
        }
        if (!$to) {
            $to = date('Y-m-d');
        }
        $id_raw = DB::raw("$id_column AS id");
        $start = DB::table($table)
            ->select($id_raw)
            ->where($time_column, '>=', $from)
            ->first();
        if (!isset($start->id)) {
            return false;
        }
        $end = DB::table($table)
            ->select($id_raw)
            ->where($time_column, '<=', $to)
            ->orderBy($id_column, 'DESC')
            ->first();
        if (!isset($end->id)) {
            return false;
        }
        return [$start->id, $end->id];
    }

    public function getAgentsForView()
    {
        $user = Auth::user();

        if ($user->agent_id == 0) {
            return Agent::select(['id', 'username'])->get();
        }

        return [
            'id' => $user->id,
            'username' => $user->username,
        ];
    }

    protected function getProviderForView()
    {
        $res = Provider::get();

        return $res->map(function ($row) {
            return [
                'id' => $row->id,
                'name' => $row->name,
            ];
        });
    }

    protected function getGameForView()
    {
        $res = Slot::get();

        return $res->map(function ($row) {
            return [
                'id' => $row->game_id,
                'name' => $row->namecn,
            ];
        });
    }

    public function betHistoryView()
    {
        return view('report.bet_history', [
            'userAgentId' => Auth::user()->agent_id,
            'agents' => $this->getAgentsForView(),
        ]);
    }

    public function betHistoryList(Request $request)
    {
        # new check
        $request->validate([
            'agent' => 'required|array',
            'agent.*' => 'exists:agent,id',
            'member' => 'nullable|string',
            'startedAt' => 'nullable|date_format:"Y-m-d H:i:s"',
            'finishedAt' => 'nullable|date_format:"Y-m-d H:i:s"',
        ]);

        $user = Auth::user();
        if ($user->agent_id != 0) {
            if ($request->agent[0] != $user->agent_id) {
                abort(403, 'Unauthorized action.');
            }
        }

        $agents = $request->agent;

        $range_ids = $this->getIdsByTimeRange('wager', 'id', 'datetime', $request->startedAt, $request->finishedAt);
        if (!$range_ids) {
            return response([
                'data' => null,
            ]);
        }
        $conditions = [];
        if (strlen($request->member) > 0) {
            $conditions[] = ['username', 'like', "%$request->member%"];
        }
        $conditions[] = ['id', '>=', $range_ids[0]];
        $conditions[] = ['id', '<=', $range_ids[1]];
        $conditions[] = ['status', '=', 1];
        $roundNum = 2;

        $res = Wager::with(['agent', 'account'])
            ->where($conditions)
            ->whereIn('agent_id', $agents)
            ->orderBy('id', 'DESC')
            ->paginate($request->perPage);

        // $slots = (new Slot)->names();
        $slots = Slot::select('*')
            ->selectRaw('CONCAT( provider_id , "_" , game_id ) AS game_code')
            ->get()
            ->keyBy('game_code');

        $res = [
            'data' => $res->map(function ($row) use ($slots, $roundNum) {
                $game_code = "{$row->provider_id}_{$row->game_code}";
                return [
                    'ticket_id' => $row->id,
                    'agent' => $row->agent->username,
                    'username' => $row->account->username,
                    'currency' => $row->currency,
                    'game' => $slots[$game_code]->name ?? $game_code,
                    'bet' => number_format($row->stake, $roundNum),
                    'payout'    => number_format($row->payout, $roundNum),
                    'win'       => number_format($row->win, $roundNum),
                    'datetime'  => $row->datetime,
                ];
            }),
            'page' => $res->currentPage(),
            'perPage' => $res->perPage(),
            'total' => $res->total(),
            'lastPage' => $res->lastPage(),
        ];

        // $this->logQuery('BetHistory');

        return response($res);
    }

    public function winloseView()
    {
        $userAgentId = Auth::user()->agent_id;
        return view('report.winlose', [
            'userAgentId' => $userAgentId,
            'agents' => $this->getAgentsForView(),
        ]);
    }

    public function winLoseList(Request $request)
    {
        # new check
        $request->validate([
            'agent' => 'required|array',
            'agent.*' => 'exists:agent,id',
            'groupby' => 'required|in:agent,member,day,provider,game',
            'startedAt' => 'nullable|date_format:Y-m-d',
            'finishedAt' => 'nullable|date_format:Y-m-d',
        ]);

        $user = Auth::user();
        if ($user->agent_id != 0) {
            if ($request->agent[0] != $user->agent_id) {
                abort(403, 'Unauthorized action.');
            }
        }

        $agents  = $request->agent;
        $rows    = [];
        $footers = [];
        $conditions = [];
        $range_ids  = $this->getIdsByTimeRange('wager', 'id', 'date', $request->startedAt, $request->finishedAt);
        if (!$range_ids) {
            return response([
                'data' => [
                    'rows'   => $rows,
                    'footer' => $footers,
                ]
            ]);
        }
        $conditions[] = ['id', '>=', $range_ids[0]];
        $conditions[] = ['id', '<=', $range_ids[1]];
        $conditions[] = ['status', '=', 1];

        $groupby = 'agent_id';
        $orderby = 'win';
        $order   = 'DESC';

        $query = Wager::with(['agent', 'account', 'provider']);

        switch ($request->groupby) {
            case 'agent':
                $groupby = 'agent_id';
                $query->select('agent_id');
                break;
            case 'member':
                $groupby = 'account_id';
                $query->select('agent_id', 'account_id')->groupBy('agent_id');
                break;
            case 'day':
                $groupby = 'date';
                $orderby = 'date';
                $query->select('date');
                break;
            case 'game':
                $groupby = 'game_code';
                $query->select('game_code');
                break;
            case 'provider':
                $groupby = 'provider_id';
                $query->select('provider_id');
                break;
        }

        $totals  = [];
        if ($wagers = $query->addSelect(
            'currency',
            'type',
            DB::raw('COUNT(DISTINCT(account_id)) AS player'),
            DB::raw('COUNT(id) AS ticket'),
            DB::raw('SUM(stake) AS stake'),
            DB::raw('SUM(payout) AS payout'),
            DB::raw('SUM(win) AS win')
        )
            ->where($conditions)
            ->whereIn('agent_id', $agents)
            ->orderBy($orderby, $order)
            ->groupBy('currency')->groupBy('type')->groupBy($groupby)
            ->get()
        ) {
            $slots = (new Slot)->names();
            foreach ($wagers as $wager) {
                $type     = $this->helper->getTypeName($wager->type);
                $currency = $wager->currency;
                if (!isset($totals[$currency][$type])) {
                    $totals[$currency][$type] = ['bet' => 0, 'payout' => 0, 'win' => 0, 'player' => 0, 'ticket' => 0];
                }

                switch ($request->groupby) {
                    case 'agent':
                        $name = $wager->agent->username;
                        break;
                    case 'member':
                        $name = '[' . $wager->agent->username . '] ' . $wager->account->username;
                        break;
                    case 'day':
                        $name = $wager->date;
                        break;
                    case 'game':
                        $game_code = $wager->provider_id . '_' . $wager->game_code;
                        $name = $slots[$game_code]['name'] ?? $game_code;
                        break;
                    case 'provider':
                        $name = $wager->provider->name;
                        break;
                }

                $totals[$currency][$type]['player'] += $wager->player;
                $totals[$currency][$type]['ticket'] += $wager->ticket;
                $totals[$currency][$type]['bet']    += $wager->stake;
                $totals[$currency][$type]['payout'] += $wager->payout;
                $totals[$currency][$type]['win']    += $wager->win;

                $margin = 0;
                if ($wager->stake > 0) {
                    $margin = ($wager->win / $wager->stake) * -100;
                }

                $rows[] = [
                    'name'     => $name,
                    'currency' => $currency,
                    'player'   => $wager->player,
                    'ticket'   => (int) $wager->ticket,
                    'bet'      => number_format($wager->stake, 2),
                    'payout'   => number_format($wager->payout, 2),
                    'win'      => number_format($wager->win, 2),
                    'margin'   => number_format($margin, 2) . '%',
                    'type'     => $type
                ];
            }
        }

        // $this->logQuery('WinLoss');

        foreach ($totals as $currency => $types) {
            foreach ($types as $type => $total) {
                $margin = 0;
                if ($total['bet'] > 0) {
                    $margin = ($total['win'] / $total['bet']) * -100;
                }
                $footers[] = [
                    'currency' => $currency,
                    'player'   => $total['player'],
                    'ticket'   => (int) $total['ticket'],
                    'bet'      => number_format($total['bet'], 2),
                    'payout'   => number_format($total['payout'], 2),
                    'win'      => number_format($total['win'], 2),
                    'margin'   => number_format($margin, 2) . '%',
                    'type'     => $type,
                ];
            }
        }

        return response([
            'data' => [
                'rows'   => $rows,
                'footer' => $footers,
            ],
        ]);
    }

    public function betDetail(Request $request)
    {
        $wager = Wager::select('ref_id')->whereId($request->ticket_id)->first();
        if (!$wager) {
            return null;
        }
        $wagerIfun = WagerIfun::select('bet_id')->whereId($wager->ref_id)->first();
        if (!$wagerIfun) {
            return null;
        }
        $host   = env('IFUN_GAME_API_URL');
        $random = $this->helper->createGUID();
        $hash   = md5(env('IFUN_GAME_API_SECRET') . $random);
        $curl = new Curl();
        $curl->setHeader('Content-Type', 'application/json');
        $curl->setHeader('Content-Random', $random);
        $curl->setHeader('Content-Hash', $hash);
        $curl->get($host . '/api/bettoken/' . $wagerIfun->bet_id);
        $result = json_decode($curl->response, true);
        if (!$result || !isset($result['token'])) {
            return response();
        }
        return response($host . '/betreport/' . $result['token']);
    }

    public function transferView()
    {
        return view('report.transfer', [
            'agents' => $this->getAgentsForView(),
            'userAgentId' => Auth::user()->agent_id,
        ]);
    }

    public function transferList(Request $request)
    {
        # new check
        $request->validate([
            'agent' => 'required|array',
            'agent.*' => 'exists:agent,id',
            'member' => 'nullable|string',
            'startedAt' => 'nullable|date_format:Y-m-d H:i:s',
            'finishedAt' => 'nullable|date_format:Y-m-d H:i:s',
        ]);

        $user = Auth::user();
        if ($user->agent_id != 0) {
            if ($request->agent[0] != $user->agent_id) {
                abort(403, 'Unauthorized action.');
            }
        }

        $agents = $request->agent;

        $range_ids = $this->getIdsByTimeRange('transfer', 'id', 'created_at', $request->startedAt, $request->finishedAt);
        if (!$range_ids) {
            return response([
                'data' => null,
            ]);
        }
        $conditions = [];
        if (strlen($request->member) > 0) {
            $conditions[] = ['username', 'like', "%$request->member%"];
        }
        $conditions[] = ['id', '>=', $range_ids[0]];
        $conditions[] = ['id', '<=', $range_ids[1]];

        $transfers = Transfer::with(['agent', 'account'])
            ->where($conditions)
            ->whereIn('agent_id', $agents)
            ->orderBy('id', 'DESC')
            ->paginate($request->perPage);

        $res = [
            'data' => $transfers->map(function ($transfer) {
                $amount = $transfer->amount;
                $credit = '';
                $debit  = '';
                if ($amount > 0) {
                    $credit = number_format($amount, 2);
                } else if ($amount < 0) {
                    $debit = number_format($amount, 2);
                }

                return [
                    'ref_id'   => $transfer->ref_id,
                    'agent'    => $transfer->agent->username,
                    'username' => $transfer->account->username,
                    'currency' => $transfer->currency,
                    'credit'   => $credit,
                    'debit'    => $debit,
                    'balance'  => number_format($transfer->balance, 2),
                    'datetime' => (string) $transfer->created_at,
                ];
            }),
            'page' => $transfers->currentPage(),
            'perPage' => $transfers->perPage(),
            'total' => $transfers->total(),
            'lastPage' => $transfers->lastPage(),
        ];

        // $this->logQuery('Transfer');

        return response($res);
    }

    public function allReportView()
    {
        return view('report.all_report', [
            'agents' => $this->getAgentsForView(),
            'userAgentId' => Auth::user()->agent_id,
        ]);
    }

    public function allReportList(Request $request)
    {
        # new check
        $request->validate([
            'agent' => 'required|array',
            'agent.*' => 'exists:agent,id',
            'member' => 'nullable|string',
            'startedAt' => 'nullable|date_format:Y-m-d H:i:s',
            'finishedAt' => 'nullable|date_format:Y-m-d H:i:s',
        ]);

        $user = Auth::user();
        if ($user->agent_id != 0) {
            if ($request->agent[0] != $user->agent_id) {
                abort(403, 'Unauthorized action.');
            }
        }

        $agents = $request->agent;

        $range_ids = $this->getIdsByTimeRange('ledger', 'id', 'created_at', $request->startedAt, $request->finishedAt);
        if (!$range_ids) {
            return response([
                'data' => null,
            ]);
        }
        $conditions = [];
        if (strlen($request->member) > 0) {
            if ($accountid = Account::whereNickname($request->member)->first()) {
                $conditions[] = ['account_id', '=', $accountid->id];
            } else {
                $conditions[] = ['account_id', '=', 0];
            }
        }
        $conditions[] = ['status', '=', 1];
        $conditions[] = ['id', '>=', $range_ids[0]];
        $conditions[] = ['id', '<=', $range_ids[1]];

        $ledgers = Ledger::with(['agent', 'provider', 'account'])
            ->where($conditions)
            ->whereIn('agent_id', $agents)
            ->orderBy('id', 'DESC')
            ->paginate($request->perPage);

        $res = [
            'data' => $ledgers->map(function ($ledger) {
                $amount = $ledger->amount;
                $credit = number_format(0, 2);
                $debit  = number_format(0, 2);
                if ($amount > 0) {
                    $credit = number_format($amount, 2);
                } elseif ($amount < 0) {
                    $debit  = number_format($amount, 2);
                }

                return [
                    'ref_id'   => $ledger->ref_id,
                    'provider' => $ledger->provider->name ?? '$TRANS$',
                    'agent'    => $ledger->agent->username,
                    'username' => $ledger->account->nickname,
                    'currency' => $ledger->currency,
                    'credit'   => $credit,
                    'debit'    => $debit,
                    'datetime' => (string) $ledger->created_at,
                    'end_balance' => number_format($ledger->end_balance, 2)
                ];
            }),
            'page' => $ledgers->currentPage(),
            'perPage' => $ledgers->perPage(),
            'total' => $ledgers->total(),
            'lastPage' => $ledgers->lastPage(),
        ];

        // $this->logQuery('AllReport');

        return response($res);
    }

    public function walletView(Request $request)
    {
        return view('report.wallet', [
            'providers' => $this->getProviderForView(),
            'games'     => $this->getGameForView(),
            'agents'    => $this->getAgentsForView()
        ]);
    }

    public function walletList(Request $request)
    {
        # new check
        $request->validate([
            'providers' => 'required|array',
            'providers.*' => 'exists:provider,id',
            'games' => 'required|array',
            'games.*' => 'exists:slot,game_id',
            'status' => 'required|in:1,0',
        ]);

        $user = Auth::user();
        if ($user->agent_id != 0) {
            if ($request->agent[0] != $user->agent_id) {
                abort(403, 'Unauthorized action.');
            }
        }

        # get agents
        $agents = [];
        foreach ($this->getAgentsForView() as $agent) {
            $agents[] = $agent['id'];
        }

        $wallets = WalletGame::with(['account.agent', 'getProvider'])
            ->where('status', $request->status)
            ->whereIn('game_id', $request->games)
            ->whereIn('provider', $request->providers)
            ->orderBy('id', 'DESC')
            ->paginate($request->perPage);

        $slots = (new Slot)->names();

        return response([
            'data' => $wallets->map(function ($wallet) use ($agents, $slots) {
                if (in_array($wallet->account->agent->id, $agents)) {
                    $game_code = $wallet->provider . '_' . $wallet->game_id;
                    return [
                        'id'        => $wallet->id,
                        'provider'  => $wallet->getProvider->name,
                        'agent'     => $wallet->account->agent->username,
                        'account'   => $wallet->account->username,
                        'game'      => $slots[$game_code]['name'] ?? $game_code,
                        'amount'    => number_format($wallet->amount, 2),
                        'status'    => $wallet->status,
                        'statusNum' => $wallet->status,
                        'datetime'  => $wallet->created_at,
                    ];
                }
            }),
            'page' => $wallets->currentPage(),
            'perPage' => $wallets->perPage(),
            'total' => $wallets->total(),
            'lastPage' => $wallets->lastPage(),
        ]);
    }

    public function deposit(Request $request)
    {
        $user = Auth::user();
        $agent_id = $user->agent_id;
        $type = $user->type;

        if ($agent_id != 0 || $type != 1) {
            return response()->json([
                'status' => 0,
                'msg' => '權限不足以還款'
            ]);
        }

        $wallet_id = $request->input('wallet_id');
        $wallet = WalletGame::find($wallet_id);
        if (!$wallet) {
            return response()->json(['status' => 14,  'msg' => 'WalletID not found']);
        }
        if ($wallet->status == 1) {
            return response()->json(['status' => 12,  'msg' => 'WalletID is deposited']);
        }
        if ($wallet->amount <= 0) {
            $wallet->status = 2;
            $wallet->save();
            return response()->json(['status' => 0]);
        }
        $wallet->status = 0;
        $wallet->save();

        $curl = new Curl();
        $curl->setHeader('Content-Type', 'application/json');
        $curl->setHeader('charset', 'UTF-8');
        $curl->post(
            env('IFUN_API_URL') . '/admin/ifun/force_deposit',
            $this->helper->hashInput([
                'wallet_id' => $wallet->id,
                'amount' => $wallet->amount,
                'mark'   => '-force@' . time()
            ], env('IFUN_SECRET'))
        );
        if (!$curl->rawResponse) {
            return response()->json(['status' => 99, 'msg' => 'Request Error']);
        }
        return response()->json(json_decode($curl->rawResponse));
    }

    public function allReportSWView()
    {
        return view('report.all_report_sw', [
            'agents' => $this->getAgentsForView(),
            'providers' => $this->getProviderForView(),
            'userAgentId' => Auth::user()->agent_id,
        ]);
    }

    public function allReportSWList(Request $request)
    {
        $request->validate([
            'agents' => 'required|array',
            'agents.*' => 'exists:agent,id',
            'providers' => 'required|array',
            'providers.*' => 'exists:provider,id',
        ]);

        $range_ids = $this->getIdsByTimeRange('ledger_external', 'id', 'created_at', $request->startedAt, $request->finishedAt);
        if (!$range_ids) {
            return response([
                'data' => null,
            ]);
        }
        $conditions = [];
        $conditions[] = ['id', '>=', $range_ids[0]];
        $conditions[] = ['id', '<=', $range_ids[1]];

        $query = LedgerExternal::with(['agent', 'account', 'provider'])
            ->whereIn('agent_id', $request->agents)
            ->whereIn('provider_id', $request->providers)
            ->where($conditions)
            ->orderBy('id', 'DESC');

        $account = $request->account ?? '';
        if ($account) {
            $query->whereHas('account', function ($query) use ($account) {
                $query->where('username', 'like', "%$account%");
            });
        }

        $status = $request->status ?? 'all';
        if ($status != 'all') {
            $query->where('status', $status);
        }

        $perPage = intval($request->perPage ?? 25);
        $res = $query->paginate($perPage);

        return response([
            'data' => $res->map(function ($row) {
                return [
                    'id' => $row->id,
                    'ref_id' => $row->ref_id,
                    'type' => $row->type,
                    'provider' => $row->provider->name,
                    'agent' => $row->agent->username,
                    'account' => $row->account->username,
                    'currency' => $row->currency,
                    'amount' => $row->amount,
                    'status' => $row->status,
                    'datetime' => $row->created_at->format('Y-m-d H:i:s'),
                ];
            }),
            'page' => $res->currentPage(),
            'perPage' => $res->perPage(),
            'total' => $res->total(),
            'lastPage' => $res->lastPage(),
        ]);
    }
}
