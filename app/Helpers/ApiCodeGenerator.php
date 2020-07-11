<?php

namespace App\Helpers;

class ApiCodeGenerator
{
    public static function generateApiCode()
    {
        return sha1(time() . env('APP_KEY') . auth()->user()->company_id);
    }
}
