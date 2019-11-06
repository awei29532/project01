<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogController extends Controller
{
    public function logView()
    {
        return view('log');
    }

    public function list(Request $request)
    {
        $query = UserLog::with('user')->orderBy('id', 'desc');

        $user = Auth::user();
        if (!$user->hasRole('admin')) {
            if ($user->hasRole('agent')) {
                $userId = User::select('id')
                    ->distinct()
                    ->where('agent_id', $user->agent_id)
                    ->get()
                    ->map(function ($row) {
                        return $row->id;
                    });
                $query->whereIn('user_id', $userId);
            } elseif ($user->hasRole(['agent_sub', 'admin_sub'])) {
                $userId = $user->id;
                $query->where('user_id', $userId);
            }
        }

        $account = $request->account ?? '';
        if ($account) {
            $query->whereHas('user', function ($q) use ($account) {
                $q->where('username', 'like', "%$account%");
            });
        }

        $startedAt = $request->startedAt ?? '';
        if ($startedAt) {
            $query->where('created_at', '>=', $startedAt);
        }

        $finishedAt = $request->finishedAt ?? '';
        if ($finishedAt) {
            $query->where('created_at', '<=', $finishedAt);
        }

        $perPage = intval($request->perPage ?? 25);
        $res = $query->paginate($perPage);

        return response([
            'data' => $res->map(function ($row) {
                $content = $row->content ?? [];
                if (isset($content['status'])) {
                    $content['status'] = $content['status'] ? trans('common.active') : trans('common.suspended');
                }

                if (isset($content['has_fun'])) {
                    $content['has_fun'] = $content['has_fun'] ? trans('games.has_fun') : trans('games.not_has_fun');
                }

                if (isset($content['role'])) {
                    $content['role'] = trans('users.' . $content['role']);
                }

                return [
                    'id' => $row->id,
                    'username' => $row->user->username,
                    'content' => trans($row->event, $content),
                    'ip' => $row->ip,
                    'device' => '',
                    'created_at' => $row->created_at,
                ];
            }),
            'page' => $res->currentPage(),
            'perPage' => $res->perPage(),
            'total' => $res->total(),
            'lastPage' => $res->lastPage(),
        ]);
    }
}
