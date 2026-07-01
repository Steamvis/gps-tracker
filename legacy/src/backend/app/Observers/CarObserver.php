<?php

namespace App\Observers;

use App\Models\Car\Car;
use App\Models\Company;

class CarObserver
{
    /**
     * Handle the car "created" event.
     *
     * @param Car $car
     */
    public function created(Car $car)
    {
        Company::updateCarsCounter($car->company);
    }

    /**
     * Handle the car "updated" event.
     *
     * @param Car $car
     */
    public function updated(Car $car)
    {
        //
    }

    /**
     * Handle the car "deleted" event.
     *
     * @param Car $car
     */
    public function deleted(Car $car)
    {
        Company::updateCarsCounter($car->company);
    }

    /**
     * Handle the car "restored" event.
     *
     * @param Car $car
     */
    public function restored(Car $car)
    {
        //
    }

    /**
     * Handle the car "force deleted" event.
     *
     * @param Car $car
     */
    public function forceDeleted(Car $car)
    {
        //
    }
}
