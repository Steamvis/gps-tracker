<?php

namespace App\Http\Middleware;

use Closure;

class ForceJsonResponse
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $request->headers->set('Accept', 'Application/json');
        return $next($request);
    }
}
