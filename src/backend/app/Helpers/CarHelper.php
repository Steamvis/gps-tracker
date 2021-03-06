<?php

namespace App\Helpers;

use App\Models\Car\Car;
use App\Models\Car\CarPoint;
use App\Models\Car\CarRoute;
use Illuminate\Support\Collection;

class CarHelper
{
    public static function checkAuthUserOwnsCar(Car $car): bool
    {
        return $car->company_id === auth()->user()->company_id;
    }
}
