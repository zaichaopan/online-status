<?php

namespace Zaichaopan\OnlineStatus\Middleware;

use Closure;
use Illuminate\Http\Request;

class UserOnline
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        optional($request->user())->online();

        return $next($request);
    }
}
