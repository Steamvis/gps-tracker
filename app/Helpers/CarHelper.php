<?php

namespace App\Helpers;

use App\Models\Car\Car;
use App\Models\Car\CarPoint;
use App\Models\Car\CarRoute;
use Illuminate\Support\Collection;

class CarHelper
{
    public static function getLatestCarPoints(Car $car): Collection
    {
        return CarPoint::whereCarId($car->id)->latest()->limit(2)->get()->reverse()->values();
    }

    public static function checkUserOwnsCar(Car $car): bool
    {
        return $car->company_id === auth()->user()->company_id;
    }

    public static function getLastRoute(Car $car): CarRoute
    {
        return $car->routes->last();
    }
}
