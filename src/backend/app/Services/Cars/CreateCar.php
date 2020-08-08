<?php

namespace App\Services\Cars;

use App\Helpers\ApiCodeGenerator;
use App\Models\Car\Car;
use App\Models\Company;
use App\Services\AbstractBaseService;
use App\Services\FileSystem\UploadImage;

class CreateCar extends AbstractBaseService
{
    public function execute(array $data): Car
    {
        $user = auth()->user();

        if (isset($data['image'])) {
            $data['image_path'] = app(UploadImage::class)->execute([
                'image' => $data['image']
            ]);
        }

        $data['api_code'] = ApiCodeGenerator::generateApiCode();

        $car = Car::create($data);

        Company::updateCarsCounter($car->company);

        return Car::find($car->id);
    }
}
