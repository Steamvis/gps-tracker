<?php

namespace App\Services\FileSystem;

use App\Models\Car\Car;
use App\Services\AbstractBaseService;
use Illuminate\Support\Facades\Storage;

class DestroyImage extends AbstractBaseService
{
    public function rules(): array
    {
        return [
            'car'   => 'required|integer|exists:cars,id',
            'image' => 'required|string'
        ];
    }

    public function execute(array $data)
    {
        $this->validate($data);

        $car     = Car::findOrFail($data['car']);
        $pattern = '/(uploads).*(jpeg|png|jpg)$/ui';

        preg_match($pattern, $data['image'], $image);
        $imagePath = 'public' . DIRECTORY_SEPARATOR . $image[0];

        if ($car->company_id === auth()->user()->company_id) {
            Storage::delete($imagePath);
            $result = Storage::exists($imagePath) ? false : true;

            if ($result) {
                $car->update(['image_path' => '']);
            }
            return true;
        }

        return false;
    }
}
