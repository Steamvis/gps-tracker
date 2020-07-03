<?php

namespace App\Services\Map\Points;

use App\CarRoute;
use App\Models\CarPoint;
use App\Services\AbstractBaseService;
use Illuminate\Validation\Rule;

class CreatePoint extends AbstractBaseService
{
    private array $data = [];

    public function rules(): array
    {
        return [
            'car_id'    => 'required|integer|exists:cars,id',
            'api_code'  => [
                'required',
                'string',
                'size:10',
                Rule::exists('cars', 'api_code')->where('id', $this->data['car_id'])
            ],
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
        ];
    }

    public function execute(array $data): CarPoint
    {
        $this->data = $data;

        $this->validate($data);

        $currentPoint = CarPoint::create($data);

        $car    = $currentPoint->car;
        $points = CarPoint::where('car_id', $car->id)->latest()->limit(2)->get();
        foreach ($points as $index => $point) {
            if ($index + 1 < 2) {
                $first = $points[$index + 1];
                $last  = $points[$index];

                $interval = ($last->created_at)->diffAsCarbonInterval($first->created_at);

                CarRoute::create([
                    'car_id'         => $first->car_id,
                    'start_point_id' => $first->id,
                    'end_point_id'   => $last->id,
                    'moving_time_ru' => $interval->locale('ru')->forHumans(),
                    'moving_time_en' => $interval->locale('en')->forHumans(),
                ]);
            }
        }

        return $currentPoint;
    }
}
