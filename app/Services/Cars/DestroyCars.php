<?php

namespace App\Services\Cars;

use App\Models\Car;
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
        $this->validate($data);

        return Car::destroy($data['action']);
    }
}
