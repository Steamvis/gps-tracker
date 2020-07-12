<?php

namespace App\Services\Cars;

use App\Helpers\CarHelper;
use App\Models\Car\Car;
use App\Models\Company;
use App\Services\AbstractBaseService;

class DestroyCar extends AbstractBaseService
{
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:cars,id'
        ];
    }

    public function execute(array $data): bool
    {
        $car = Car::findOrFail($data['id']);

        if (CarHelper::checkUserOwnsCar($car)) {
            $this->validate($data);

            $car->delete();

            Company::updateCarsCounter(auth()->user()->company);

            return true;
        }

        return false;
    }
}
