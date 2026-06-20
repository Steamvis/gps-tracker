<?php

use App\Models\User;
use Faker\Generator as Faker;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(User::class, function (Faker $faker) {
    return [
//        'company_id'        => $faker->randomNumber(),
        'first_name'        => $faker->firstName,
        'last_name'         => $faker->lastName,
        'gender'            => $faker->randomElement(['male', 'female']),
        'locale'            => app()->getLocale(),
        'level'             => User::LEVEL_UNVERIFIED,
        'email'             => $faker->unique()->safeEmail,
//        'email_verified_at' => Carbon::now(),
        'password'          => bcrypt($faker->password),
//        'remember_token'    => Str::random(10),
        'created_at'        => Carbon::now(),
        'updated_at'        => Carbon::now(),
    ];
});
