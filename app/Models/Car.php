<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Car extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'mark_id',
        'driver_id',
        'color',
        'manager_id',
        'company_id',
        'name',
        'year',
        'vin_number',
        'gov_number',
        'description',
        'api_code',
    ];

    public function getVinNumberAttribute(): ?string
    {
        return !$this->attributes['vin_number'] ? __('dashboard.general.unknown') : $this->attributes['vin_number'];
    }

    public function getGovNumberAttribute(): ?string
    {
        return !$this->attributes['gov_number'] ? __('dashboard.general.unknown') : $this->attributes['gov_number'];
    }

    public function getYearAttribute(): ?string
    {
        return !$this->attributes['year'] ? __('dashboard.general.unknown') : $this->attributes['year'];
    }

    public function getApiCodeAttribute(): string
    {
        return "{$this->attributes['api_code']}_{$this->attributes['id']}";
    }

    public function company(): HasOne
    {
        return $this->hasOne(Company::class, 'id', 'company_id');
    }

    public function brand(): HasOne
    {
        return $this->hasOne(CarMark::class, 'id', 'mark_id');
    }

    public function points(): HasMany
    {
        return $this->hasMany(CarPoint::class);
    }
}
