<?php

namespace App\Services\Cars;

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
        $this->validate($data);

        $car = Car::findOrFail($data['id']);

        if ($car->company->owner_id === auth()->user()->id) {
            $car->delete();

            Company::updateCarsCounter(auth()->user()->company);

            return true;
        }

        return false;
    }
}
