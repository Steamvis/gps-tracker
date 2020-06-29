<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;

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

    public function brands()
    {
        return $this->hasMany(CarMark::class);
    }

    public function getFlagAttribute(): string
    {
        return URL::asset($this->attributes['flag_path']);
    }

    public function getNameAttribute()
    {
        $name = 'name_' . app()->getLocale();
        return $this->attributes[$name];
    }
}
