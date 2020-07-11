<?php

namespace App\Services\FileSystem;

use App\Services\AbstractBaseService;

class UploadImage extends AbstractBaseService
{
    public function rules(): array
    {
        return [
            'image' => 'image|mimes:jpeg,jpg,png',
        ];
    }

    public function execute(array $data): ?string
    {
        $this->validate($data);

        $directory = 'uploads' . DIRECTORY_SEPARATOR . 'company_' . auth()->user()->id;

        return $data[0]->store($directory, 'public');
    }
}
