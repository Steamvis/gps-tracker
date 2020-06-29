<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Car extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'mark_id',
        'driver_id',
        'manager_id',
        'company_id',
        'name',
        'year',
        'vin_number',
        'gov_number',
        'description'
    ];

    public function getVinNumberAttribute()
    {
        return !$this->attributes['vin_number'] ? __('dashboard.general.unknown') : $this->attributes['vin_number'];
    }

    public function getGovNumberAttribute()
    {
        return !$this->attributes['gov_number'] ? __('dashboard.general.unknown') : $this->attributes['gov_number'];
    }

    public function getYearAttribute()
    {
        return !$this->attributes['year'] ? __('dashboard.general.unknown') : $this->attributes['year'];
    }

    public function company()
    {
        return $this->hasOne(Company::class, 'id', 'company_id');
    }

    public function brand(): HasOne
    {
        return $this->hasOne(CarMark::class, 'id', 'mark_id');
    }
}
