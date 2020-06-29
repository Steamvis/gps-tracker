<?php

namespace App\Services\Cars;

use App\Models\Car;
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

        return $car->delete();
    }
}
