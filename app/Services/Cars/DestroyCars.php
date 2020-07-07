<?php

namespace App\Services\Cars;

use App\Models\Car\Car;
use App\Services\AbstractBaseService;

class DestroyCars extends AbstractBaseService
{
    public function rules(): array
    {
        return [
            'action' => 'required|array|exists:cars,id'
        ];
    }

    public function execute(array $data): bool
    {
        $data['action'] = explode(',', $data['action'][0]);

        $this->validate($data);

        foreach ($data['action'] as $id) {
            if (Car::find($id)->company->owner_id !== auth()->user()->id) {
                return false;
            }
        }

        Car::destroy($data['action']);

        $user = auth()->user();
        $user->company::updateCarsCounter($user->company);

        return true;
    }
}
