<?php

namespace App\Http\Middleware;

use Closure;

class SetLangs
{
    /**
     * Undocumented function
     *
     * @param Request $request
     * @param  \Closure  $next
     * @return void
     */
    public function handle($request, Closure $next)
    {
        app()->setLocale($request->session()->get('lang', 'en'));
        return $next($request);
    }
}