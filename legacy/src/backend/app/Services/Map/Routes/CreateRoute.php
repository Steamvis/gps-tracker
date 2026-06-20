<?php

namespace App\Services\Map\Routes;

use App\Models\Car\CarRoute;
use App\Services\AbstractBaseService;

class CreateRoute extends AbstractBaseService
{
    public function rules(): array
    {
        return [
            'car_id' => 'required|integer|exists:cars,id',
            'start_time' => 'required|date',
            'end_time' => 'date'
        ];
    }

    public function execute(array $data)
    {
        $this->validate($data);

        return CarRoute::create($data);
    }
}
