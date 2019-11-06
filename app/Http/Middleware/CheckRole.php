<?php

namespace App\Http\Middleware;

use Closure;
use Spatie\Permission\Exceptions\UnauthorizedException;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $role)
    {
        $roles = is_array($role)
            ? $role
            : explode('|', $role);
        foreach ($roles as $r) {
            if (app('auth')->user()->hasRole($r)) {
                return $next($request);
            }
        }
        throw UnauthorizedException::forRoles($roles);
    }
}
