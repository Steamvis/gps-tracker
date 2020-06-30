<?php

use App\Models\Car;
use App\Models\CarMark;
use App\Models\Company;
use Faker\Generator as Faker;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(Car::class, function (Faker $faker) {
    return [
        'mark_id'    => 2,
//        'company_id' => Company::all()->random()->id,
        'color'      => $faker->hexColor,
        'name'       => $faker->name,
        'year'       => $faker->year,
    ];
});
