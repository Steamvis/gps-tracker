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
        $car = Car::find($data['id']);

        if (CarHelper::checkAuthUserOwnsCar($car)) {
            $this->validate($data);

            $car->delete();

            return true;
        }

        return false;
    }
}
