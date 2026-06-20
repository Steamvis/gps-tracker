<?php

namespace App\Helpers\Map;

use App\DTO\MapDTO;
use App\Models\Car\Car;
use App\Models\Car\CarRoute;
use App\Services\Map\Points\CreatePoint;
use App\Services\Map\Routes\CreateRoute;

class RouteGenerator
{
    public function generate(MapDTO $DTO): void
    {
        if ($this->isStartRoute($DTO)) {
            $this->createRoute($DTO);
        }

        $this->createPoint($DTO);
        $this->createPointsRelation($DTO);

        if ($this->isEndRoute($DTO)) {
            $this->endRoute($DTO->getCar()->last_route);
        }
    }

    private function createRoute(MapDTO $DTO): void
    {
        app(CreateRoute::class)->execute([
            'name'       => 'TEST',
            'car_id'     => $DTO->getCar()->id,
            'start_time' => now()
        ]);
    }

    private function createPoint(MapDTO $DTO): void
    {
        app(CreatePoint::class)->execute([
            'car_id'    => $DTO->getCar()->id,
            'api_code'  => $DTO->getApiCode(),
            'latitude'  => $DTO->getLatitude(),
            'longitude' => $DTO->getLongitude(),
        ]);
    }

    private function createPointsRelation(MapDTO $DTO): void
    {
        $points = Car::lastPoints($DTO->getCar());

        if ($points->count() > 1) {
            $startPoint = $points[0];
            $endPoint = $points[1];

            app(SectionGenerator::class)->generate($startPoint, $endPoint);
        }
    }

    private function endRoute(CarRoute $route): void
    {
        $route->update(['end_time' => now()]);
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
