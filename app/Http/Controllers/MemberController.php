<?php

namespace App\Http\Controllers;

use App\Events\UserEvent;
use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MemberController extends Controller
{
    public function memberView()
    {
        return view('accounts.member');
    }

    public function memberList()
    {
        $data = request()->all();
        
        $query = Account::with('agent');
        
        $agent_id = Auth::user()->agent_id;
        if ($agent_id) {
            $query->where('agent_id', $agent_id);
        }

        $status = $data['status'] ?? 'all';
        if ($status != 'all') {
            $query->where('status', $status);
        }

        $account = $data['account'] ?? '';
        if ($account) {
            $query->where('username', 'like', "%$account%");
        }

        $agent = $data['agent'] ?? '';
        if ($agent) {
            $query->whereHas('agent', function ($q) use ($agent) {
                $q->where('username', 'like', "%$agent%");
            });
        }

        $res = $query->paginate($data['perPage']);

        return response([
            'data' => $res->map(function ($row) {
                return [
                    'id' => $row->id,
                    'username' => $row->username,
                    'agent' => $row->agent->username,
                    'currency' => $row->currency,
                    'balance' => number_format($row->balance, 2),
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

    public function enabledMember(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:account,id',
            'enabled' => 'required|in:1,0',
        ]);

        $user = Account::find($request->id);
        $user->status = $request->enabled;
        $user->save();

        event(new UserEvent($request, 'event.member.enabled', [
            'username' => $user->username,
            'status' => $request->enabled,
        ]));
    }
}
