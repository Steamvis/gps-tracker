<?php

namespace App\Services\Cars;

use App\Helpers\CarHelper;
use App\Models\Car\Car;
use App\Models\Company;
use App\Services\AbstractBaseService;

class DestroyCars extends AbstractBaseService
{
    public function rules(): array
    {
        return [
            'action' => 'required|array|exists:cars,id'
        ];
    }

    public function execute(array $data): bool
    {
        $data['action'] = explode(',', $data['action'][0]);

        $this->validate($data);

        foreach ($data['action'] as $id) {
            $car = Car::find($id);

            if (!CarHelper::checkAuthUserOwnsCar($car)) {
                return false;
            }

            Company::updateCarsCounter(auth()->user()->company);
        }

        return Car::destroy($data['action']);
    }
}
