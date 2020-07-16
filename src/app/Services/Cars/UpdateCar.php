<?php


namespace App\Services\Cars;


use App\Helpers\CarHelper;
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

    public function execute(array $data): bool
    {
        $user = auth()->user();

        $car = Car::findOrFail($data['id']);

        if (CarHelper::checkAuthUserOwnsCar($car)) {
            $data['vin_number'] = $data['vin_number'] === __('dashboard.general.unknown') ? '' : $data['vin_number'];

            if (isset($data['image'])) {
                $data['image_path'] = app(UploadImage::class)->execute([
                    'image' => $data['image']
                ]);
            }

            $this->validate($data);
            $car->update($data);

            return true;
        }
        return false;
    }
}
