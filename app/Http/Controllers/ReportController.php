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

    public function getAgentsForView()
    {
        $user = Auth::user();

        if ($user->hasRole('admin')) {
            return Agent::select(['id', 'username'])->get();
        }

        return [
            [
                'id' => $user->agent_id,
                'username' => $user->username,
            ]
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
        $res = Provider::with('game')->get();

        return $res->map(function ($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'games' => $p->game->map(function ($g) {
                    return [
                        'id' => $g->id,
                        'game_id' => $g->game_id,
                        'code' => $g->game_code,
                        'name' => $g->name,
                    ];
                }),
            ];
        });
    }

    public function betHistoryView()
    {
        return view('report.bet_history', [
            'agents' => $this->getAgentsForView(),
            'games' => $this->getGameForView(),
        ]);
    }

    public function betHistoryList(Request $request)
    {
        # new check
        $request->validate([
            'agent' => 'required|array',
            'startedAt' => 'nullable|date_format:"Y-m-d H:i:s"',
            'finishedAt' => 'nullable|date_format:"Y-m-d H:i:s"',
        ]);

        $user = Auth::user();
        if ($user->hasRole(['agent', 'agent_sub'])) {
            if ($request->agent[0] != $user->agent_id) {
                abort(403, 'Unauthorized action.');
            }
        }

        $conditions = [];
        $conditions[] = ['status', '=', 1];
        if (strlen($request->member) > 0) {
            $conditions[] = ['username', 'like', "%$request->member%"];
        }

        if ($request->game) {
            $conditions[] = ['provider_id', $request->game['providerId']];
            $conditions[] = ['game_code', $request->game['gameId']];
        }
        
        if ($request->startedAt) {
            $conditions[] = ['datetime', '>=', $request->startedAt];
        }
        
        if ($request->finishedAt) {
            $conditions[] = ['datetime', '<=', $request->finishedAt];
        }
        
        $agents = $request->agent;
        $res = Wager::with(['agent', 'account'])
            ->where($conditions)
            ->whereIn('agent_id', $agents)
            ->orderBy('id', 'DESC');

        // $this->logQuery('BetHistory');

        return $res;
    }

    public function winloseView()
    {
        return view('report.winlose', [
            'agents' => $this->getAgentsForView(),
        ]);
    }

    public function winLoseList(Request $request)
    {
        # new check
        $request->validate([
            'agent' => 'required|array',
            'groupby' => 'required|in:agent,member,day,provider,game',
            'startedAt' => 'nullable|date_format:Y-m-d',
            'finishedAt' => 'nullable|date_format:Y-m-d',
        ]);

        $user = Auth::user();
        if ($user->hasRole(['agent', 'agent_sub'])) {
            if ($request->agent[0] != $user->agent_id) {
                abort(403, 'Unauthorized action.');
            }
        }

        $agents  = $request->agent;
        $rows    = [];
        $footers = [];
        $conditions = [];
        $conditions[] = ['status', '=', 1];
        
        if ($request->startedAt) {
            $conditions[] = ['datetime', '>=', $request->startedAt];
        }
        
        if ($request->finishedAt) {
            $conditions[] = ['datetime', '<=', $request->finishedAt];
        }

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
                    'bet'      => floatval($wager->stake),
                    'payout'   => floatval($wager->payout),
                    'win'      => floatval($wager->win),
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
                    'bet'      => floatval($total['bet']),
                    'payout'   => floatval($total['payout']),
                    'win'      => floatval($total['win']),
                    'margin'   => number_format($margin, 2) . '%',
                    'type'     => $type,
                ];
            }
        }

        return [
            'data' => [
                'rows'   => $rows,
                'footer' => $footers,
            ],
        ];
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
        ]);
    }

    public function transferList(Request $request)
    {
        # new check
        $request->validate([
            'agent' => 'required|array',
            'member' => 'nullable|string',
            'startedAt' => 'nullable|date_format:Y-m-d H:i:s',
            'finishedAt' => 'nullable|date_format:Y-m-d H:i:s',
        ]);

        $user = Auth::user();
        if ($user->hasRole(['agent', 'agent_sub'])) {
            if ($request->agent[0] != $user->agent_id) {
                abort(403, 'Unauthorized action.');
            }
        }
        
        $conditions = [];
        if (strlen($request->member) > 0) {
            $conditions[] = ['username', 'like', "%$request->member%"];
        }
        
        if ($request->startedAt) {
            $conditions[] = ['created_at', '>=', $request->startedAt];
        }
        
        if ($request->finishedAt) {
            $conditions[] = ['created_at', '<=', $request->finishedAt];
        }
        
        $agents = $request->agent;
        $query = Transfer::with(['agent', 'account'])
            ->where($conditions)
            ->whereIn('agent_id', $agents)
            ->orderBy('id', 'DESC');

        // $this->logQuery('Transfer');

        return $query;
    }

    public function allReportView()
    {
        return view('report.all_report', [
            'agents' => $this->getAgentsForView(),
        ]);
    }

    public function allReportList(Request $request)
    {
        # new check
        $request->validate([
            'agent' => 'required|array',
            'member' => 'nullable|string',
            'startedAt' => 'nullable|date_format:Y-m-d H:i:s',
            'finishedAt' => 'nullable|date_format:Y-m-d H:i:s',
        ]);

        $user = Auth::user();
        if ($user->hasRole(['agent', 'agent_sub'])) {
            if ($request->agent[0] != $user->agent_id) {
                abort(403, 'Unauthorized action.');
            }
        }

        $conditions = [];
        $conditions[] = ['status', '=', 1];

        if (strlen($request->member) > 0) {
            if ($accountid = Account::whereNickname($request->member)->first()) {
                $conditions[] = ['account_id', '=', $accountid->id];
            } else {
                $conditions[] = ['account_id', '=', 0];
            }
        }
        
        if ($request->startedAt) {
            $conditions[] = ['created_at', '>=', $request->startedAt];
        }
        
        if ($request->finishedAt) {
            $conditions[] = ['created_at', '<=', $request->finishedAt];
        }
        
        $agents = $request->agent;
        $query = Ledger::with(['agent', 'provider', 'account'])
            ->where($conditions)
            ->whereIn('agent_id', $agents)
            ->orderBy('id', 'DESC');

        // $this->logQuery('AllReport');

        return $query;
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
            'status' => 'required|in:1,0',
        ]);

        $user = Auth::user();

        $query = WalletGame::with(['account.agent', 'getProvider'])
            ->where('status', $request->status)
            ->whereIn('provider', $request->providers)
            ->orderBy('id', 'DESC');

        if ($request->game) {
            $query->where('game_id', $request->game);
        }

        $agents = $request->agents;
        if ($user->hasRole(['agent', 'agent_sub'])) {
            $agents = [$user->agent_id];
        }
        $query->whereHas('account', function ($q) use ($agents) {
            $q->whereIn('agent_id', $agents);
        });
        
        return $query;
    }

    public function deposit(Request $request)
    {
        $user = Auth::user();

        if (!$user->hasRole(['admin', 'admin_sub', 'agent'])) {
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
        $wallet->status = 0;
        $wallet->save();

        $curl = new Curl();
        $curl->setHeader('Content-Type', 'application/json');
        $curl->setHeader('charset', 'UTF-8');
        $curl->post(
            env('API_URL') . '/admin/force_deposit',
            json_encode($this->helper->hashInput([
                'account_id' => $wallet->account_id,
                'wallet_id' => $wallet->id,
                'amount' => $wallet->amount,
            ], env('API_ADMIN_SECRET')))
        );
        if (!$curl->response) {
            return response()->json(['status' => 99, 'msg' => 'Request Error']);
        }
        return response()->json(json_decode($curl->response));
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
            'providers' => 'required|array',
        ]);

        $conditions = [];
        
        if ($request->startedAt) {
            $conditions[] = ['created_at', '>=', $request->startedAt];
        }
        
        if ($request->finishedAt) {
            $conditions[] = ['created_at', '<=', $request->finishedAt];
        }

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

        return $query;
    }

    public function list(Request $request, $type)
    {
        switch ($type) {
            case 'win_lose':
                return response($this->winLoseList($request));
            case 'bet_history':
                $query = $this->betHistoryList($request);
                $res = $query->paginate($request->perPage);
                $data = $this->mapReportData($res, $type);
                break;
            case 'all_report':
                $query = $this->allReportList($request);
                $res = $query->paginate($request->perPage);
                $data = $this->mapReportData($res, $type);
                break;
            case 'all_report_sw':
                $query = $this->allReportSWList($request);
                $res = $query->paginate($request->perPage);
                $data = $this->mapReportData($res, $type);
                break;
            case 'transfer':
                $query = $this->transferList($request);
                $res = $query->paginate($request->perPage);
                $data = $this->map($res, $type);
                break;
            case 'wallet':
                $query = $this->walletList($request);
                $res = $query->paginate($request->perPage);
                $data = $this->mapReportData($res, $type);
                break;
        }

        return response([
            'data' => $data,
            'page' => $res->currentPage(),
            'perPage' => $res->perPage(),
            'total' => $res->total(),
            'lastPage' => $res->lastPage(),
        ]);
    }

    public function exportExcel(Request $request, $type)
    {
        switch ($type) {
            case 'win_lose':
                # 100
                break;
            case 'bet_history':
                # 7
                break;
            case 'all_report':
                # 7
                break;
            case 'all_report_sw':
                # 7
                break;
        }
    }

    public function mapReportData($data, $type)
    {
        switch ($type) {
            case 'bet_history':
                $slots = Slot::select('*')
                    ->selectRaw('CONCAT( provider_id , "_" , game_id ) AS game_code')
                    ->get()
                    ->keyBy('game_code');
                return $data->map(function ($row) use ($slots) {
                    $game_code = "{$row->provider_id}_{$row->game_code}";
                        return [
                            'ticket_id' => $row->id,
                            'agent' => $row->agent->username,
                            'username' => $row->account->username,
                            'currency' => $row->currency,
                            'game' => $slots[$game_code]->name ?? $game_code,
                            'bet' => floatval($row->stake),
                            'payout'    => floatval($row->payout),
                            'win'       => floatval($row->win),
                            'datetime'  => $row->datetime,
                        ];
                    });
            case 'all_report':
                return $data->map(function ($ledger) {
                    return [
                        'ref_id'   => $ledger->ref_id,
                        'provider' => $ledger->provider->name ?? '$TRANS$',
                        'agent'    => $ledger->agent->username,
                        'username' => $ledger->account->nickname,
                        'currency' => $ledger->currency,
                        'amount'   => floatval($ledger->amount),
                        'datetime' => (string) $ledger->created_at,
                        'end_balance' => floatval($ledger->end_balance)
                    ];
                });
            case 'all_report_sw':
                return $data->map(function ($row) {
                    return [
                        'id' => $row->id,
                        'ref_id' => $row->ref_id,
                        'type' => $row->type,
                        'provider' => $row->provider->name,
                        'agent' => $row->agent->username,
                        'account' => $row->account->username,
                        'currency' => $row->currency,
                        'amount' => number_format($row->amount, 2),
                        'status' => $row->status,
                        'datetime' => $row->created_at->format('Y-m-d H:i:s'),
                    ];
                });
                break;
            case 'transfer':
                return $data->map(function ($transfer) {
                    return [
                        'ref_id'   => $transfer->ref_id,
                        'agent'    => $transfer->agent->username,
                        'username' => $transfer->account->username,
                        'currency' => $transfer->currency,
                        'amount'   => floatval($transfer->amount),
                        'balance'  => floatval($transfer->balance),
                        'datetime' => (string) $transfer->created_at,
                    ];
                });
                break;
            case 'wallet':
                $slots = (new Slot)->names();
                return $data->map(function ($wallet) use ($slots) {
                    $game_code = $wallet->provider . '_' . $wallet->game_id;
                    return [
                        'id'        => $wallet->id,
                        'provider'  => $wallet->getProvider->name,
                        'agent'     => $wallet->account->agent->username,
                        'account'   => $wallet->account->username,
                        'game'      => $slots[$game_code]['name'] ?? $game_code,
                        'status'    => $wallet->status,
                        'amount'    => floatval($wallet->amount),
                        'datetime'  => $wallet->created_at,
                    ];
                });
                break;
        }
    }
}
