<?php

namespace App\Models\Car;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CarRoute extends Model
{
    protected $table = 'cars_routes';

    public $fillable = [
        'name',
        'car_id',
        'start_time',
        'end_time'
    ];

    public function getRouteNameAttribute(): string
    {
        // TODO
    }

    public function getMovingTimeAttribute(): string
    {
        $format    = 'Y-m-d H:i';
        $startTime = Carbon::parse($this->attributes['start_time'])->format($format);
        $endTime   = Carbon::parse($this->attributes['end_time'])->format($format);

        return "{$startTime} - {$endTime}";
    }

    public function getStartAttribute(): object
    {
        $point = $this->sections->first()->points->first();
        return (object)[
            'latitude'  => $point->latitude,
            'longitude' => $point->longitude
        ];
    }

    public function getEndAttribute(): object
    {
        $point = $this->sections->last()->points->last();
        return (object)[
            'latitude'  => $point->latitude,
            'longitude' => $point->longitude
        ];
    }

    public function car(): HasOne
    {
        return $this->hasOne(Car::class, 'id', 'car_id');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(CarRouteSection::class, 'route_id', 'id');
    }

    public function points(): HasMany
    {
        return $this->hasMany(CarPoint::class, 'route_id', 'id');
    }
}
