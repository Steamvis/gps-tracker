<?php

namespace App\Services\Cars;

use App\Helpers\CarHelper;
use App\Models\Car\Car;
use App\Models\Company;
use App\Services\AbstractBaseService;

class DestroyCars extends AbstractBaseService
{
    public function execute(array $data): bool
    {
        $data['action'] = explode(',', $data['action'][0]);

        foreach ($data['action'] as $id) {
            $car = Car::find($id);

            if (!CarHelper::checkAuthUserOwnsCar($car)) {
                return false;
            }
        }

        return Car::destroy($data['action']);
    }
}
