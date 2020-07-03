<?php

namespace App\Models\Car;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CarPoint extends Model
{
    public $table = 'cars_points';

    public $fillable = [
        'car_id',
        'route_id',
        'latitude',
        'longitude',
    ];

    public function car(): HasOne
    {
        return $this->hasOne(Car::class, 'id', 'car_id');
    }

    public function route(): HasOne
    {
        return $this->hasOne(CarRoute::class, 'id', 'route_id');
    }
}
