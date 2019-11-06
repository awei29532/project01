<?php

namespace App\Http\Controllers;

use App\Events\UserEvent;
use App\Models\Slot;
use DB;
use Curl\Curl;
use App\Models\Provider;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function gameView()
    {
        $provider = Provider::get();
        return view('products.game', [
            'provider' => $provider->map(function ($row) {
                    return [
                        'id' => $row->id,
                        'name' => $row->name,
                    ];
                }),
        ]);
    }
    
    public function gameList(Request $request)
    {
        $query = Slot::with('provider');

        $request->validate([
            'providers' => 'required|array',
            'providers.*' => 'exists:provider,id',
        ]);

        $name = $request->name ?? '';
        if ($name) {
            $query->where('name', 'like', "%$name%");
        }

        $status = $request->status ?? 'all';
        if ($status != 'all') {
            $query->where('status', $status);
        }
        
        $has_fun = $request->has_fun ?? 'all';
        if ($has_fun != 'all') {
            $query->where('has_fun', $has_fun);
        }

        $providers = $request->providers;
        $query->whereIn('provider_id', $providers);

        $perPage = intval($request->perPage ?? 25);
        $res = $query->paginate($perPage);

        return response([
            'data' => $res->map(function ($row) {
                    return [
                        'id' => $row->id,
                        'provider_id' => $row->provider_id,
                        'provider_name' => $row->provider->name,
                        'game_id' => $row->game_id,
                        'game_code' => $row->game_code,
                        'name' => $row->name,
                        'has_fun' => $row->has_fun,
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
    
    public function addGame(Request $request)
    {
        $this->validation($request, 'add');

        $provider = Provider::find($request->provider_id);

        $slot = new Slot();
        $slot->provider_id = $request->provider_id;
        $slot->game_id = $request->game_id;
        $slot->game_code = ($provider->code . $request->game_id);
        $slot->name = $request->name;
        $slot->namecn = $request->name;
        $slot->has_fun = $request->has_fun;
        $slot->status = 1;
        $slot->save();

        event(new UserEvent($request, 'event.game.add', [
            'provider' => $provider->name,
            'name' => $slot->name,
        ]));
    }

    public function gameEditView($id = 0)
    {
        $providers = Provider::get();
        if ($id != 0) {
            $game = Slot::find($id);
        }
        return view('products.game_edit', [
            'game_id' => $id,
            'providers' => $providers->map(function ($row) {
                    return [
                        'id' => $row->id,
                        'name' => $row->name,
                    ];
                }),
            'game' => $id != 0 ? $game : [],
        ]);
    }
    
    public function editGame(Request $request)
    {
        $this->validation($request, 'edit');

        $slot = Slot::find($request->id);
        $slot->name = $request->name;
        $slot->has_fun = $request->has_fun;
        $slot->save();

        event(new UserEvent($request, 'event.game.edit', [
            'name' => $slot->name,
            'has_fun' => $request->has_fun,
        ]));
    }

    public function toggleEnabled(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:slot,id',
            'enabled' => 'required|in:0,1',
        ]);

        $slot = Slot::find($request->id);
        $slot->status = $request->enabled;
        $slot->save();

        event(new UserEvent($request, 'event.game.enabled', [
            'name' => $slot->name,
            'status' => $request->enabled,
        ]));
    }

    private function validation($data, $type)
    {
        $validation = [
            'name' => 'required|string',
            'has_fun' => 'required|in:0,1',
        ];

        if ($type == 'edit') {
            $validation['id'] = 'required|exists:slot,id';
        } elseif ($type == 'add') {
            $validation['game_id'] = 'required|string';
            $validation['provider_id'] = 'required|exists:provider,id';
        }

        $data->validate($validation);
    }

    public function updateGameList(Request $request)
    {
        $provider_id = request()->input('id');

        if (!$provider_id) {
            throw new Exception("provider_id is null.");
        }

        $provider_code = Provider::find($provider_id)->code ?? 0;

        if (!$provider_code) {
            throw new Exception("provider_code is null.");
        }

        $url = env('IFUN_GAME_API_URL') . '/list/';
        $curl = new Curl();
        $curl->get($url);
        $res = $curl->response;

        $games = Slot::where('provider_id', 1)
            ->get()
            ->keyBy('game_id')
            ->toArray();

        $new_games = [];
        foreach ($res as $row) {
            if (count($games)) {
                foreach ($games as $game) {
                    if ($row->gid == $game['game_id']) {
                        $game['status'] = $row->status;
                    } elseif (!isset($new_games[$row->gid]) && !isset($games[$row->gid])) {
                        $new_games[$row->gid] = [
                            'provider_id' => $provider_id,
                            'game_id' => $row->gid,
                            'game_code' => "{$provider_code}{$row->gid}",
                            'name' => $row->name,
                            'namecn' => $row->name,
                            'has_fun' => $row->demo,
                            'status' => $row->status,
                        ];
                    }
                }
            } else {
                $new_games[$row->gid] = [
                    'provider_id' => 1,
                    'game_id' => $row->gid,
                    'game_code' => "IF{$row->gid}",
                    'name' => $row->name,
                    'namecn' => $row->name,
                    'has_fun' => $row->demo,
                    'status' => $row->status,
                ];
            }
        }

        $games = array_merge(array_values($games), array_values($new_games));

        $colums = [
            'provider_id',
            'game_id',
            'game_code',
            'name',
            'namecn',
            'has_fun',
            'status',
        ];
        $colums_str = implode(', ', $colums);

        $games_str_arr = [];
        foreach ($games as $game) {
            $arr = [];
            foreach ($colums as $key) {
                switch ($key) {
                    case 'game_id':
                    case 'game_code':
                    case 'name':
                    case 'namecn':
                        $arr[$key] = "\"{$game[$key]}\"";
                        break;
                    default:
                        $arr[$key] = $game[$key];
                        break;
                }
            }
            $games_str_arr[] = "(" . implode(', ', $arr) . ")";
        }
        $games_value = implode(', ', $games_str_arr);
        $sql = "REPLACE INTO `Slot` ( {$colums_str} ) VALUE {$games_value} ";
        DB::update($sql);

        event(new UserEvent($request, 'event.game.update-game-list'));
    }
}
