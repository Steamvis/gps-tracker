<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarPoint extends Model
{
    public $table = 'cars_points';

    public $fillable = [
        'car_id',
        'start_route_time',
        'end_route_time',
        'latitude',
        'longitude',
    ];

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }

}
