<?php


namespace App\Services\Cars;


use App\Models\Car\Car;
use App\Services\AbstractBaseService;
use App\Services\FileSystem\UploadImage;

class UpdateCar extends AbstractBaseService
{
    public function rules(): array
    {
        return array_merge(app(CreateCar::class)->rules(), [
            'id' => 'integer|exists:cars,id'
        ]);
    }

    public function execute(array $data)
    {
        $user = auth()->user();

        $data['vin_number'] = $data['vin_number'] === __('dashboard.general.unknown') ? '' : $data['vin_number'];

        if (isset($data['image'])) {
            $data['image_path'] = app(UploadImage::class)->execute([$data['image']]);
        }

        $this->validate($data);
        $car = Car::findOrFail($data['id'])->update($data);

        return $car;
    }
}
