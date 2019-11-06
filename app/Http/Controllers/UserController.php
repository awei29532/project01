<?php

namespace App\Http\Controllers;

use App\Events\UserEvent;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function userView()
    {
        $roles = Role::select(['id', 'name'])->where('name', '!=', 'admin')->get();
        return view('accounts.user', [
            'roles' => $roles,
        ]);
    }

    public function userList(Request $request)
    {
        $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id',
        ]);

        $query = User::role($request->roles);

        $account = $request->account ?? '';
        if ($account) {
            $query->where('username', 'like', "%$account%");
        }

        $perPage = intval($request->perPage ?? 25);
        $res = $query->paginate($perPage);

        return response([
            'data' => $res->map(function ($row) {
                return [
                    'id' => $row->id,
                    'username' => $row->username,
                    'status' => $row->status,
                    'role' => $row->getRoleNames()[0],
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

    public function updateUserRole(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:user,id',
        ]);

        $user = User::find($request->id);
        $user->removeRole($user->getRoleNames()[0]);
        $user->assignRole($request->role);

        event(new UserEvent($request, 'event.user.update-user-role', [
            'username' => $user->username,
            'role' => $user->getRoleNames()[0],
        ]));
    }

    public function updateAllUser(Request $request)
    {
        $users = User::get();
        foreach ($users as $user) {
            if ($user->hasAnyRole(['admin', 'admin_sub', 'agent', 'agent_sub'])) {
                continue;
            }

            if ($user->type == 1) {
                $user->assignRole('agent');
            } elseif ($user->type == 2) {
                $user->assignRole('agent_sub');
            }
        }

        event(new UserEvent($request, 'event.user.update-all-user-role'));
    }

    public function toggleEnabled(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:user,id',
            'enabled' => 'required|in:1,0',
        ]);

        $user = User::find($request->id);
        $user->status = $request->enabled;
        $user->save();

        event(new UserEvent($request, 'event.user.enabled', [
            'username' => $user->username,
            'status' => $user->status,
        ]));
    }
}
