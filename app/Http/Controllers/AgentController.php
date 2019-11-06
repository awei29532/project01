<?php

namespace App\Http\Controllers;

use App\Events\AgentEvent;
use App\Events\UserEvent;
use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\AgentDetail;
use App\Models\AgentSetting;
use App\Models\AgentWallet;
use App\Models\Currency;
use App\Models\Provider;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Hash;

class AgentController extends Controller
{
    public function agentView()
    {
        return view('accounts.agent');
    }

    public function agentList()
    {
        $data = request()->all();

        $query = Agent::with('detail')->with('settings.provider')->with('members');

        $status = $data['status'] ?? 'all';
        if ($status != 'all') {
            $query->where('status', $status);
        }

        $account = $data['account'];
        if ($account) {
            $query->where('username', 'like', "%$account%");
        }

        $res = $query->paginate($data['perPage']);

        return response([
            'data' => $res->map(function ($row) {
                # provider
                $prodivers = [];
                foreach ($row->settings as $setting) {
                    if ($setting->status && $setting->provider->status) {
                        $prodivers[] = $setting->provider->name;
                    }
                }

                return [
                    'id' => $row->id,
                    'username' => $row->username,
                    'name' => $row->detail->name,
                    'currency' => $row->currency,
                    'products' => implode(', ', $prodivers),
                    'members' => $row->members->count(),
                    'status' => $row->status,
                    'remark' => $row->detail->remark,
                    'updated_at' => $row->updated_at->format('Y-m-d H:i:s'),
                    'created_at' => $row->created_at->format('Y-m-d H:i:s'),
                ];
            }),
            'page' => $res->currentPage(),
            'perPage' => $res->perPage(),
            'total' => $res->total(),
            'lastPage' => $res->lastPage(),
        ]);
    }

    public function agentEditView($id = 0)
    {
        $currencies = Currency::select(
                'id',
                'code'
            )->where('status', 1)
            ->get();
        $providers = Provider::select(
                'id',
                'code',
                'name',
                'status'
            )->where('status', 1)
            ->get();

        $settings = [];
        if ($id) {
            $agent = Agent::with('detail')
                ->with('settings.provider')
                ->with('wallet')
                ->find($id);

            $settings = $agent->settings->map(function ($setting) {
                return [
                    'id' => $setting->provider_id,
                    'status' => $setting->status,
                    'percent' => $setting->percentage,
                    'code' => $setting->provider->code,
                ];
            })->keyBy('code');
        }

        return view('accounts.agent_edit', [
            'currencies' => $currencies,
            'providers' => $providers,
            'agent_id' => $id,
            'agent' => [
                'username' => $agent->username ?? null,
                'password' => $agent->password ?? null,
                'name' => $agent->detail->name ?? null,
                'currency' => $agent->currency ?? null,
                'remark' => $agent->detail->remark ?? null,
                'auth_mode' => $agent->auth_mode ?? 1,
                'callback' => $agent->callback ?? null,
                'wallet_mode' => $agent->wallet_mode ?? 0,
                'url_balance' => $agent->wallet->url_balance ?? null,
                'url_deposit' => $agent->wallet->url_deposit ?? null,
                'url_withdrawal' => $agent->wallet->url_withdrawal ?? null,
                'url_rollback' => $agent->wallet->url_rollback ?? null,
                'configs' => $providers->map(function ($provider) use ($settings) {
                    return [
                        'id' => $provider->id,
                        'code' => $provider->code,
                        'name' => $provider->name,
                        'status' => $settings[$provider->code]['status'] ?? 0,
                        'percent' => $settings[$provider->code]['percentage'] ?? 0,
                    ];
                }),
            ],
        ]);
    }

    public function addAgent(Request $request)
    {
        $this->checkAgentData($request);

        $agent = new Agent();
        $agent->username = $request->username;
        $agent->currency = $request->currency;
        $agent->parent_id = 0;
        $agent->key = hash_hmac('md5', $agent->username . $agent->currency, $agent->username);
        $agent->secret = md5($agent->username . $agent->currency . $agent->key . Carbon::now()->timestamp);
        $agent->status = 1;
        $agent->auth_mode = $request->auth_mode;
        $agent->callback = $request->callback;
        $agent->wallet_mode = $request->wallet_mode;
        $agent->save();

        $wallet = new AgentWallet();
        $wallet->agent_id = $agent->id;
        $wallet->type = $request->wallet_mode ? 1 : 0;
        $wallet->url_balance = $request->url_balance;
        $wallet->url_deposit = $request->url_deposit;
        $wallet->url_withdrawal = $request->url_withdrawal;
        $wallet->url_rollback = $request->url_rollback;
        $wallet->save();

        $agentDetail = new AgentDetail();
        $agentDetail->agent_id = $agent->id;
        $agentDetail->created_by = Auth::id();
        $agentDetail->created_user = Auth::user()->username;
        $agentDetail->created_ip = $request->ip();
        $agentDetail->name = $request->name;
        $agentDetail->remark = $request->remark;
        $agentDetail->save();

        $user = new User();
        $user->username = $request->username;
        $user->agent_id = $agent->id;
        $user->type = 1;
        $user->name = $request->name;
        $user->status = 1;
        $user->password = Hash::make($request->password);
        $user->save();

        $user->assignRole('agent');

        $today = date('Y-m-d');
        $inserts = [];
        foreach ($request->configs as $config) {
            $inserts[] = [
                'agent_id' => $agent->id,
                'provider_id' => $config['id'],
                'percentage' => $config['percent'],
                'status' => $config['status'],
                'effective' => $today,
            ];
        }
        AgentSetting::insert($inserts);

        event(new UserEvent($request, 'event.agent.add', [
            'username' => $request->username,
        ]));

        event(new AgentEvent($request->username));
    }

    public function editAgent(Request $request)
    {
        $this->checkAgentData($request, 'edit');

        $agent = Agent::find($request->id);
        $agent->key = hash_hmac('md5', $agent->username . $agent->currency, $agent->username);
        $agent->secret = md5($agent->username . $agent->currency . $agent->key . Carbon::now()->timestamp);
        $agent->auth_mode = $request->auth_mode;
        $agent->callback = $request->callback;
        $agent->wallet_mode = $request->wallet_mode;
        $agent->save();

        AgentWallet::where('agent_id', $request->id)->update([
            'url_balance' => $request->url_balance,
            'url_deposit' => $request->url_deposit,
            'url_withdrawal' => $request->url_withdrawal,
            'url_rollback' => $request->url_rollback,
        ]);

        AgentDetail::where('agent_id', $request->id)->update([
            'name' => $request->name,
            'remark' => $request->remark,
        ]);

        $user_update = [
            'name' => $request->name,
        ];
        if ($request->password) {
            $user_update['password'] = Hash::make($request->password);
        }
        User::where('username', $request->username)->update($user_update);

        // # update settings
        $configs = $request->configs;
        $settings = AgentSetting::where('agent_id', $request->id)->get();
        $settings->each(function ($setting, $key) use ($request, &$configs) {
            foreach ($configs as $config_key => $config) {
                if ($setting->provider_id == $config['id']) {
                    $configs[$config_key]['update'] = true;
                    AgentSetting::where('agent_id', $request->id)
                        ->where('provider_id', $config['id'])
                        ->update([
                            'percentage' => $config['percent'],
                            'status' => $config['status'],
                        ]);
                }
            }
        });

        $insert = [];
        $today = date('Y-m-d');
        foreach ($configs as $config) {
            if (!isset($config['update'])) {
                $insert[] = [
                    'agent_id' => $agent->id,
                    'provider_id' => $config['id'],
                    'percentage' => $config['percent'],
                    'status' => $config['status'],
                    'effective' => $today,
                ];
            }
        }
        AgentSetting::insert($insert);

        event(new UserEvent($request, 'event.agent.edit', [
            'username' => $request->username,
        ]));

        event(new AgentEvent($request->username));
    }

    public function enabledAgent(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:agent,id',
            'enabled' => 'required|in:1,0',
        ]);

        $agent = Agent::find($request->id);
        $agent->status = $request->enabled;
        $agent->save();

        event(new UserEvent($request, 'event.agent.enabled', [
            'username' => $agent->username,
            'status' => $request->enabled,
        ]));

        event(new AgentEvent($agent->username));
    }

    private function checkAgentData($request, $type = 'add')
    {
        $validationArr = [
            'username' => 'required|string|alpha_num|unique:agent,username',
            'name' => 'required|string',
            'currency' => 'required|in:RMB,TWD,VND,MYR,JPY',
            'remark' => 'nullable|string',
            'auth_mode' => 'required|in:1,0',
            'callback' => 'required_if:auth_mode,0',
            'wallet_mode' => 'required|in:1,0',
            'url_balance' => 'required_if:wallet_mode,1',
            'url_deposit' => 'required_if:wallet_mode,1',
            'url_withdrawal' => 'required_if:wallet_mode,1',
            'url_rollback' => 'required_if:wallet_mode,1',
            'configs' => 'required|array',
            'configs.*.id' => 'required|exists:provider,id',
            'configs.*.percent' => 'required|numeric|min:0|max:100',
            'confogs.*.status' => 'required|boolean',
        ];

        if ($type == 'edit') {
            $validationArr['id'] = 'required|exists:agent,id';
            unset($validationArr['username']);
            $validationArr['password'] = 'nullable|string';
        } elseif ($type == 'add') {
            $validationArr['password'] = 'required|string';
        }
        $request->validate($validationArr);
    }

    public function subView()
    {
        return view('accounts.subaccount');
    }

    public function subList()
    {
        $user = Auth::user();
        $data = request()->all();

        $query = User::with('agent')
            ->where('type', 2)
            ->where('agent_id', $user->agent_id);

        $status = $data['status'] ?? 'all';
        if ($status != 'all') {
            $query->where('status', $status);
        }

        $res = $query->paginate($data['perPage']);

        return response([
            'data' => $res->map(function ($row) {
                return [
                    'id' => $row->id,
                    'agent' => $row->agent->username ?? '',
                    'username' => $row->username,
                    'name' => $row->name,
                    'remark' => $row->remark,
                    'status' => $row->status,
                    'updated_at' => $row->updated_at->format('Y-m-d H:i:s'),
                    'created_at' => $row->created_at->format('Y-m-d H:i:s'),
                ];
            }),
            'page' => $res->currentPage(),
            'perPage' => $res->perPage(),
            'total' => $res->total(),
            'lastPage' => $res->lastPage(),
        ]);
    }

    public function subEditView ($id = 0)
    {
        if ($id) {
            $sub = User::find($id);
        }
        return view('accounts.subaccount_edit', [
            'sub_id' => $id,
            'sub' => $id ? [
                'username' => $sub->username,
                'name' => $sub->name,
                'remark' => $sub->remark,
            ] : [],
        ]);
    }

    public function addSub (Request $request)
    {
        $this->checkSubData($request);

        $user = new User();
        $user->type = 2;
        $user->username = $request->username . "@" . Auth::user()->username;
        $user->agent_id = Auth::user()->agent_id;
        $user->password = Hash::make($request->password);
        $user->name = $request->name;
        $user->remark = $request->remark;
        $user->status = 1;
        $user->save();

        if (Auth::user()->hasRole('admin')) {
            $user->assignRole('admin_sub');
        } else {
            $user->assignRole('agent_sub');
        }

        event(new UserEvent($request, 'event.sub.add', [
            'username' => $request->username,
        ]));
    }

    public function editSub (Request $request)
    {
        $this->checkSubData($request, 'edit');

        $user = User::find($request->id);
        if ($request->password) {
            $user->password = Hash::make($request->password);
        }
        $user->name = $request->name;
        $user->remark = $request->remark;
        $user->save();

        event(new UserEvent($request, 'event.sub.edit', [
            'username' => $user->username,
            'name' => $user->name,
            'remark' => $user->remark,
        ]));
    }

    public function enabledSub(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:user,id',
            'enabled' => 'required|in:1,0',
        ]);

        $user = User::find($request->id);
        $user->status = $request->enabled;
        $user->save();

        event(new UserEvent($request, 'event.sub.enabled', [
            'username' => $user->username,
            'status' => $request->enabled,
        ]));
    }

    private function checkSubData ($request, $type = 'add')
    {
        $validationArr = [
            'name' => 'required|string',
            'remark' => 'nullable|string',
        ];

        if ($type == 'edit') {
            $validationArr['password'] = 'nullable|string|alpha_num|min:6';
        } elseif ($type == 'add') {
            $validationArr['username'] = 'required|string|alpha_num|unique:user,username|min:6';
            $validationArr['password'] = 'required|string|alpha_num|min:6';
        }

        $request->validate($validationArr);
    }
}
