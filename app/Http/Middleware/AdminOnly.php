<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOnly
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle($request, Closure $next)
{
    if(auth()->user()->role != 'admin') {
        return response()->json(['error'=>'Forbidden'],403);
    }
    return $next($request);
}
}
