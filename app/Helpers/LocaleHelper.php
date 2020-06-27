<?php

namespace App\Helpers;

use Auth;

class LocaleHelper
{
    const LOCALES = ['ru', 'en'];

    public static function getLocale()
    {
        if (Auth::check()) {
            $locale = Auth::user()->locale;
        } else {
            $locale = config('app.locale');
        }
        return $locale;
    }

    public static function getSupportedLocale(?string $locale): string
    {
        return in_array($locale, static::LOCALES) ? $locale : static::getLocale();
    }
}
