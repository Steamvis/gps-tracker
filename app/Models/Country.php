<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    public $fillable = [
        'name_ru',
        'name_en',
        'code',
        'flag_path',
    ];

    public static function getCountries()
    {
        return Country::all()->collect();
    }
}
