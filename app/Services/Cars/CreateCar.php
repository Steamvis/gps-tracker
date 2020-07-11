<?php

namespace App\Services\Cars;

use App\Helpers\ApiCodeGenerator;
use App\Models\Car\Car;
use App\Models\Company;
use App\Services\AbstractBaseService;
use App\Services\FileSystem\UploadImage;

class CreateCar extends AbstractBaseService
{
    public function rules(): array
    {
        return [
            'name'        => 'required|string|min:2|max:255',
            'color'       => 'nullable|string|size:7',
            'vin_number'  => 'nullable|string|min:11|max:17',
            'gov_number'  => 'nullable|string|min:3|max:30',
            'description' => 'nullable|string|min:10|max:500',
            'image_path'  => 'nullable|string',
            'mark_id'     => 'required|integer',
            'driver_id'   => 'nullable|integer',
            'manager_id'  => 'nullable|integer',
            'year'        => 'nullable|date_format:Y',
        ];
    }

    public function execute(array $data): Car
    {
        $user = auth()->user();

        if (isset($data['image'])) {
            $data['image_path'] = app(UploadImage::class)->execute([$data['image']]);
        }

        $this->validate($data);

        $data['api_code'] = ApiCodeGenerator::generateApiCode();

        $car = Car::create($data);

        Company::updateCarsCounter($car->company);

        return Car::find($car->id);
    }
}
