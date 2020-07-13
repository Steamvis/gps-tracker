<?php

namespace App\Helpers\Map;

use App\DTO\MapDTO;
use App\Helpers\CarHelper;
use App\Models\Car\CarRoute;
use App\Services\Map\Points\CreatePoint;
use App\Services\Map\Routes\CreateRoute;

class RouteGenerator
{
    private CarRoute $route;

    public function generate(MapDTO $DTO): void
    {
        if ($this->isStartRoute($DTO)) {
            $this->route = app(CreateRoute::class)->execute([
                'name'       => 'test',
                'car_id'     => $DTO->getCar()->id,
                'start_time' => now()
            ]);
        }

        app(CreatePoint::class)->execute([
            'car_id'    => $DTO->getCar()->id,
            'api_code'  => $DTO->getApiCode(),
            'latitude'  => $DTO->getLatitude(),
            'longitude' => $DTO->getLongitude(),
        ]);

        $points = CarHelper::getLatestCarPoints($DTO->getCar());
        $startPoint = $points[0];

        if ($points->count() > 1) {
            $endPoint = $points[1];

            app(SectionGenerator::class)->generate($startPoint, $endPoint);
        }

        if ($this->isEndRoute($DTO)) {
            $this->route = CarHelper::getLastRoute($DTO->getCar());
            $this->endRoute();
        }
    }

    private function endRoute(): void
    {
        $this->route->update(['end_time' => now()]);
    }

    private function isStartRoute(MapDTO $DTO): bool
    {
        return $DTO->isStartRoute() && !$DTO->isEndRoute();
    }

    private function isEndRoute(MapDTO $DTO): bool
    {
        return !$DTO->isStartRoute() && $DTO->isEndRoute();
    }
}
