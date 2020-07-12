<?php

namespace App\Services\Map\Section;

use App\Models\Car\CarRouteSection;
use App\Services\AbstractBaseService;

class CreateSection extends AbstractBaseService
{
    public function rules(): array
    {
        return [
            'route_id' => 'required|integer',
            'moving_time_ru' => 'required|string',
            'moving_time_en' => 'required|string',
        ];
    }

    public function execute(array $data): CarRouteSection
    {
        $this->validate($data);

        return CarRouteSection::create($data);
    }
}
