<?php


use App\Models\Company;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Carbon;

/** @var Factory $factory */
$factory->define(Company::class, function (Faker $faker) {
    return [
        'country_id'    => $faker->randomNumber(),
        'title'         => $faker->word,
        'created_at'    => Carbon::now(),
        'updated_at'    => Carbon::now(),
    ];
});
