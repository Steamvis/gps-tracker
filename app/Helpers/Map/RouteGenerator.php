<?php

namespace App\Helpers\Map;

use App\Helpers\CarHelper;
use App\Models\Car\Car;
use App\Models\Car\CarRoute;
use App\Services\Map\Points\CreatePoint;
use App\Services\Map\Routes\CreateRoute;
use Illuminate\Http\Request;

class RouteGenerator
{
    protected array  $data = [];
    protected object $routeActions;

    private CarRoute $route;

    public function __construct(Request $request)
    {
        [$apiCode, $carID] = explode('_', $request->carInfo);

        $this->data = [
            'car_id'    => $carID,
            'api_code'  => $apiCode,
            'latitude'  => $request->latitude,
            'longitude' => $request->longitude
        ];

        $this->routeActions = (object)[
            'startRoute' => $request->start_route ? true : false,
            'endRoute'   => $request->end_route ? true : false
        ];
    }

    public function generate(): void
    {
        $car = Car::whereId($this->data['car_id'])->first();

        if ($this->isStartRoute()) {
            $this->route = app(CreateRoute::class)->execute([
                'name'       => 'test',
                'car_id'     => $car->id,
                'start_time' => now()
            ]);
        }

        app(CreatePoint::class)->execute($this->data);

        $points = CarHelper::getLatestCarPoints($car);
        $startPoint = $points[0];

        if ($points->count() > 1) {
            $endPoint = $points[1];



            app(SectionGenerator::class)->generate($startPoint, $endPoint);
        }


        if ($this->isEndRoute()) {
            $this->route = CarHelper::getLastRoute($car);
            $this->endRoute();
        }
    }

    private function endRoute(): void
    {
        $this->route->update(['end_time' => now()]);
    }

    private function isStartRoute(): bool
    {
        return $this->routeActions->startRoute && !$this->routeActions->endRoute;
    }

    private function isEndRoute(): bool
    {
        return !$this->routeActions->startRoute && $this->routeActions->endRoute;
    }
}
