<?php

namespace App;

use App\Models\CarPoint;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CarRoute extends Model
{
    public $table = 'cars_routes';

    public $timestamps = false;

    protected $fillable = [
        'car_id',
        'start_point_id',
        'end_point_id',
        'moving_time_ru',
        'moving_time_en'
    ];

    public function getMovingTimeAttribute()
    {
        $movingTime = 'moving_time_' . app()->getLocale();
        return $this->attributes[$movingTime];
    }

    public function start_point(): HasOne
    {
        return $this->hasOne(CarPoint::class, 'id', 'start_point_id');
    }

    public function end_point(): HasOne
    {
        return $this->hasOne(CarPoint::class, 'id', 'end_point_id');
    }
}
