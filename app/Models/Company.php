<?php

namespace App\Models;

use App\Models\Car\Car;
use App\Models\Car\CarPoint;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Company extends Model
{
    protected $fillable = [
        'owner_id',
        'country_id',
        'cars_counter',
        'staff_counter',
        'title',
        'logotype_path',
        'created_at',
    ];

    protected $casts = [
        'owner_id' => 'integer'
    ];

    public static function updateCarsCounter(Company $company): void
    {
        $company->cars_counter = $company->cars->count();
        $company->save();


        Cache::set('company_cars_counter' . $company->id, $company->cars_counter);
    }

    public function getConnectedCarsCounterAttribute()
    {
        return CarPoint::all()->filter(fn($car) => $car->created_at->diffInMinutes() < 5)->count();
    }

    public function getDisconnectedCarsCounterAttribute()
    {
        return $this->cars_counter - $this->getConnectedCarsCounterAttribute();
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cars(): HasMany
    {
        return $this->hasMany(Car::class, 'company_id', 'id');
    }
}
