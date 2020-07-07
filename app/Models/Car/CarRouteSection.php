<?php

namespace App\Models\Car;

use App\Models\Car\CarPoint;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CarRouteSection extends Model
{
    public $table = 'cars_route_sections';

    public $timestamps = false;

    protected $fillable = [
        'route_id',
        'start_point_id',
        'end_point_id',
        'moving_time_ru',
        'moving_time_en'
    ];

    public function getMovingTimeAttribute(): string
    {
        $movingTime = 'moving_time_' . app()->getLocale();
        return $this->attributes[$movingTime];
    }

    public function getStartPointAttribute(): object
    {
        $point = $this->points->first();
        return (object)[
            'latitude'  => $point->latitude,
            'longitude' => $point->longitude
        ];
    }

    public function getEndPointAttribute(): object
    {
        $point = $this->points->last();
        return (object)[
            'latitude'  => $point->latitude,
            'longitude' => $point->longitude
        ];
    }

    public function points()
    {
        return $this->belongsToMany(
            CarPoint::class,
            'points_section',
            'section_id',
            'point_id',
        );
    }

    public function route(): HasOne
    {
        return $this->hasOne(CarRoute::class, 'id', 'route_id');
    }
}
