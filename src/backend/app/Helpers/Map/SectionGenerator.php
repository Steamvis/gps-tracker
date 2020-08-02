<?php

namespace App\Helpers\Map;

use App\Exceptions\MapPointException;
use App\Models\Car\CarPoint;
use App\Models\Car\CarRouteSection;
use App\Models\Car\PointsSection;
use App\Services\Map\Section\CreateSection;
use Carbon\CarbonInterval;

class SectionGenerator
{
    private CarPoint        $startPoint;
    private CarPoint        $endPoint;
    private CarRouteSection $section;

    public function generate(CarPoint $startPoint, CarPoint $endPoint): ?CarRouteSection
    {
        $this->startPoint = $startPoint;
        $this->endPoint = $endPoint;

        if ($this->isStartPointIDGreaterEndPointID()) {
            $this->startPoint = $this->endPoint;
            $this->endPoint = $this->startPoint;
        }

        if ($this->isPointsHasOneRoute() && $this->isPointsHasOneCar()) {
            $interval = $endPoint->created_at->diffAsCarbonInterval($startPoint->created_at);

            $this->section = $this->createSection($interval);

            $this->createRelationPointSection();

            return $this->section;
        }
        return null;
    }

    private function createSection(CarbonInterval $interval): CarRouteSection
    {
        return app(CreateSection::class)->execute([
            'route_id'       => $this->endPoint->route_id,
            'moving_time_ru' => $interval->locale('ru')->forHumans(),
            'moving_time_en' => $interval->locale('en')->forHumans(),
        ]);
    }

    private function createRelationPointSection(): void
    {
        PointsSection::create([
            'section_id' => $this->section->id,
            'point_id'   => $this->startPoint->id,
        ]);
        $this->startPoint->update(['section_id' => $this->section->id]);

        PointsSection::create([
            'section_id' => $this->section->id,
            'point_id'   => $this->endPoint->id,
        ]);
        $this->endPoint->update(['section_id' => $this->section->id]);
    }

    private function isPointsHasOneRoute(): bool
    {
        return $this->startPoint->route_id === $this->endPoint->route_id;
    }

    private function isPointsHasOneCar(): bool
    {
        return $this->startPoint->car_id === $this->endPoint->car_id;
    }

    private function isStartPointIDGreaterEndPointID(): bool
    {
        return $this->startPoint->id > $this->endPoint->id;
    }
}
