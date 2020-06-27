<?php

namespace App\Http\Middleware;

use Closure;

class Company
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
        if (!$request->user()->company) {
            return \Redirect::route('company_register', app()->getLocale());
        }

        return $next($request);
    }
}
