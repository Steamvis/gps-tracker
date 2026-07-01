<?php

namespace App\Http\Middleware;

use App\Helpers\LocaleHelper;
use Closure;

class Locale
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
        $locale = LocaleHelper::getSupportedLocale($request->route('locale'));

        \App::setLocale($locale);

        \View::share(['locales' => LocaleHelper::LOCALES]);

        return $next($request);
    }
}
