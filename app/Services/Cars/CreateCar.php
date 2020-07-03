<?php

namespace App\Services\Cars;

use App\Models\Car;
use App\Models\Company;
use App\Services\AbstractBaseService;
use Illuminate\Support\Str;

class CreateCar extends AbstractBaseService
{
    public function rules(): array
    {
        return [
            'name'        => 'required|string|min:5|max:255',
            'color'       => 'nullable|string|size:7',
            'vin_number'  => 'nullable|string|min:11|max:17',
            'gov_number'  => 'nullable|string|min:3|max:30',
            'description' => 'nullable|string|min:10|max:500',
            'year'        => 'nullable|date_format:Y',
            'mark_id'     => 'required|integer',
            'driver_id'   => 'nullable|integer',
            'manager_id'  => 'nullable|integer',
        ];
    }

    public function execute(array $data): Car
    {
        $this->validate($data);

        $code = ['api_code' => Str::limit(sha1(time() . env('APP_KEY') , rand(0,100)), 10, '')];
        $data = array_merge($data, $code);

        $car = Car::create($data);

        Company::updateCarsCounter($car->company_id);

        return Car::find($car->id);
    }
}
