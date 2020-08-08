<?php

namespace App\Models\Car;

use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Car extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'mark_id',
        'color',
        'company_id',
        'name',
        'year',
        'vin_number',
        'gov_number',
        'description',
        'api_code',
        'image_path'
    ];

    public $casts = ['year' => 'integer'];

    public function scopeLastPoints(Builder $query, Car $car)
    {
        $points = $query->where('id', $car->id)->firstOrFail()
            ->points()
            ->latest()
            ->limit(2)
            ->get();

        // reverse points
        [$points[0], $points[1]] = [$points[1], $points[0]];

        return $points;
    }

    public function getLastRouteAttribute(): CarRoute
    {
        return $this->routes->last();
    }

    public function getMovingTimeAttribute(): string
    {
        $route = $this->last_route;
        $interval = Carbon::parse($route->end_time)->diffAsCarbonInterval(Carbon::parse($route->start_time));
        return $interval->locale(app()->getLocale())->forHumans();
    }

    public function getVinNumberAttribute(): string
    {
        return !$this->attributes['vin_number'] ? __('dashboard.general.unknown') : $this->attributes['vin_number'];
    }

    public function getGovNumberAttribute(): string
    {
        return !$this->attributes['gov_number'] ? __('dashboard.general.unknown') : $this->attributes['gov_number'];
    }

    public function getYearAttribute(): string
    {
        return !$this->attributes['year'] ? __('dashboard.general.unknown') : $this->attributes['year'];
    }

    public function getApiCodeAttribute(): string
    {
        $apiCode = Str::limit($this->attributes['api_code'], 10, '');
        return "{$apiCode}_{$this->attributes['id']}";
    }

    public function getBrandNameAttribute(): string
    {
        return empty($this->brand->name) ?: $this->brand->name;
    }

    public function getNameFullAttribute(): string
    {
        return "{$this->brand_name} {$this->name}";
    }

    public function getLocationAttribute(): object
    {
        $point = $this->routes->last()->points->last();

        return (object)[
            'latitude'  => $point->latitude,
            'longitude' => $point->longitude
        ];
    }

    public function getIsConnectedMapAttribute(): bool
    {
        $points = $this->points;

        if ($points->isEmpty()) {
            return false;
        }

        return $points->last()->created_at->diffInMinutes() < 5;
    }

    public function getImageAttribute(): string
    {
        return empty($this->attributes['image_path'])
            ? asset('images/map/car-point.png')
            : Storage::url($this->attributes['image_path']);
    }

    public function company(): HasOne
    {
        return $this->hasOne(Company::class, 'id', 'company_id');
    }

    public function brand(): HasOne
    {
        return $this->hasOne(CarMark::class, 'id', 'mark_id');
    }

    public function routes(): HasMany
    {
        return $this->hasMany(CarRoute::class);
    }

    public function points(): HasMany
    {
        return $this->hasMany(CarPoint::class);
    }

    public function isCurrentRoute(int $id): bool
    {
        return $this->routes->last()->id === $id;
    }
}
