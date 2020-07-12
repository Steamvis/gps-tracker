<?php

namespace App\Services\Cars;

use App\Helpers\CarHelper;
use App\Models\Car\Car;
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
            if (CarHelper::checkUserOwnsCar($car)) {
                Car::destroy($data['action']);

                $user = auth()->user();
                $user->company::updateCarsCounter($user->company);

                return true;
            }
        }

        return false;
    }
}
