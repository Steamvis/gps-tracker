<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Redirect;
use View;

class Company
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!$request->user()->company) {
            return Redirect::route('company_register', app()->getLocale());
        }

        View::share(['company' => auth()->user()->company]);

        return $next($request);
    }
}
