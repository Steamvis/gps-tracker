<?php

namespace App\Services\Map\Points;

use App\Models\Car\Car;
use App\Models\Car\CarPoint;
use App\Models\Car\CarRoute;
use App\Services\AbstractBaseService;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CreatePoint extends AbstractBaseService
{
    private array $data = [];

    public function rules(): array
    {
        return [
            'car_id' => 'required|integer|exists:cars,id',
            'api_code' => [
                'required',
                'alpha_num',
                'size:10',
                Rule::in(
                    Str::limit(
                        Car::whereId($this->data['car_id'])->first()->api_code, 10, ''
                    )
                )
            ],
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'route_id' => 'required|integer|exists:cars_routes,id'
        ];
    }

    public function execute(array $data): CarPoint
    {
        $this->data = $data;

        $route = CarRoute::whereCarId($this->data['car_id'])->get()->last();
        $this->data['route_id'] = $route->id;

        $this->validate($this->data);

        return CarPoint::create($this->data);
    }
}
