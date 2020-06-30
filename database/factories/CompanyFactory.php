<?php

use App\Models\Company;
use Faker\Generator as Faker;
use Illuminate\Support\Carbon;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(Company::class, function (Faker $faker) {
    return [
        'country_id'    => $faker->randomNumber(),
        'title'         => $faker->word,
        'created_at'    => Carbon::now(),
        'updated_at'    => Carbon::now(),
    ];
});
