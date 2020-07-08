<?php

use App\Models\Car\Car;
use App\Models\Car\CarRoute;
use App\Models\Car\CarRouteSection;
use App\Models\Car\PointsSection;
use App\Models\Company;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DemoUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = [
            'first_name'        => 'demo',
            'last_name'         => 'demoUser',
            'gender'            => 'male',
            'locale'            => 'ru',
            'level'             => User::LEVEL_COMPANY_OWNER,
            'email'             => 'demo@demo.demo',
            'password'          => Hash::make('demo'),
            'email_verified_at' => now()
        ];
        DB::table('users')->insert($user);

        $company = [
            'owner_id'      => 1,
            'country_id'    => 24,
            'cars_counter'  => 3,
            'staff_counter' => 1,
            'title'         => 'demoCompany',
        ];
        DB::table('companies')->insert($company);
        User::find(1)->update(['company_id' => 1]);


        $cars = [
            [
                'name'       => 'TGX',
                'year'       => 2020,
                'mark_id'    => 22,
                'company_id' => 1,
                'color'      => '#2f80ed',
                'vin_number' => 'WDDNF9EB8DA526479',
                'gov_number' => 'X999XE777',
                'api_code'   => sha1(time() . env('APP_KEY') . 1),
            ],
            [
                'name'       => 'FH',
                'year'       => 2020,
                'mark_id'    => 26,
                'company_id' => 1,
                'color'      => '#ffffed',
                'vin_number' => 'XUUYA755JF000015E',
                'gov_number' => 'A457OH777',
                'api_code'   => sha1(now() . env('APP_KEY') . 2)
            ],
            [
                'name'       => '1840',
                'year'       => 2019,
                'mark_id'    => 23,
                'company_id' => 1,
                'color'      => '#2f80bb',
                'vin_number' => 'XUYTA744JF012515B',
                'gov_number' => 'Е444КХ50',
                'api_code'   => sha1(time() . env('APP_KEY') . 3),
            ],
            [
                'name'       => 'TOTAL',
                'year'       => 2019,
                'mark_id'    => 12,
                'company_id' => 1,
                'color'      => '#2f80bb',
                'vin_number' => 'BEYTA744JF012515B',
                'gov_number' => 'Х453ХК126',
                'api_code'   => sha1(time() . env('APP_KEY') . 4),
            ],
            [
                'name'       => 'turismo',
                'year'       => 2001,
                'mark_id'    => 11,
                'company_id' => 1,
                'color'      => '#2f80bb',
                'vin_number' => 'XUYTL744JF012515B',
                'gov_number' => 'Н444КХ40',
                'api_code'   => sha1(time() . env('APP_KEY') . 5),
            ],
            [
                'name'       => 'XF 105',
                'year'       => 2009,
                'mark_id'    => 15,
                'company_id' => 1,
                'color'      => '#2f80bb',
                'vin_number' => 'XUYTL744JF012515B',
                'gov_number' => '',
                'api_code'   => sha1(time() . env('APP_KEY') . 6),
            ],
            [
                'name'       => 'XF 106',
                'year'       => 2009,
                'mark_id'    => 15,
                'company_id' => 1,
                'color'      => '#2f80bb',
                'vin_number' => 'XUYTL744JF012515B',
                'gov_number' => '',
                'api_code'   => sha1(time() . env('APP_KEY') . 7),
            ],
            [
                'name'       => 'XF 109',
                'year'       => 2009,
                'mark_id'    => 2,
                'company_id' => 1,
                'color'      => '#2f80bb',
                'vin_number' => 'XUYTL744JF012515B',
                'gov_number' => '',
                'api_code'   => sha1(time() . env('APP_KEY') . 8),
            ],
            [
                'name'       => 'test',
                'year'       => 2009,
                'mark_id'    => 1,
                'company_id' => 1,
                'color'      => '#2f80bb',
                'vin_number' => 'XUYTL744JF012515B',
                'gov_number' => '',
                'api_code'   => sha1(time() . env('APP_KEY') . 9),
            ],
            [
                'name'       => 'car',
                'year'       => 1998,
                'mark_id'    => 1,
                'company_id' => 1,
                'color'      => '#2f80bb',
                'vin_number' => '',
                'gov_number' => '',
                'api_code'   => sha1(time() . env('APP_KEY') . 10),
            ],
            [
                'name'       => 'car',
                'year'       => 2012,
                'mark_id'    => 25,
                'company_id' => 1,
                'color'      => '#2f80bb',
                'vin_number' => '',
                'gov_number' => '',
                'api_code'   => sha1(time() . env('APP_KEY') . 11),
            ],
        ];

        DB::table('cars')->insert($cars);

        factory(Car::class, 100)->create([
            'company_id' => 1,
        ]);

        Company::updateCarsCounter(User::find(1)->company);

        $carsRoutes            = [
            [
                'name'       => 'Калининград - заправка',
                'car_id'     => 1,
                'start_time' => Carbon::create(2020, 3, 9, 15, 43, 15),
                'end_time'   => Carbon::create(2020, 3, 9, 16, 35, 10)
            ],
            [
                'name'       => 'Финляндия аэропорт - Торговый центр',
                'car_id'     => 1,
                'start_time' => Carbon::create(2020, 4, 7, 15, 43, 15),
                'end_time'   => Carbon::create(2020, 4, 8, 16, 20, 20),
            ],
            [
                'name'       => 'аэропорт - Торговый центр',
                'car_id'     => 2,
                'start_time' => Carbon::create(2020, 4, 7, 1, 43, 15),
                'end_time'   => Carbon::create(2020, 4, 10, 16, 4, 10),
            ],
            [
                'name'       => 'Reykjavik',
                'car_id'     => 2,
                'start_time' => now()->subMinutes(10),
                'end_time'   => now(),
            ]
        ];
        $carPointsRoute1       = [
            [
                'car_id'     => 1,
                'route_id'   => 1,
                'latitude'   => 54.701640,
                'longitude'  => 20.520866,
                'created_at' => Carbon::create('2020', '3', '9', '15', '43', '15')
            ],
            [
                'car_id'     => 1,
                'route_id'   => 1,
                'latitude'   => 54.701591,
                'longitude'  => 20.521116,
                'created_at' => Carbon::create(2020, 3, 9, 15, 44, 20)
            ],
            [
                'car_id'     => 1,
                'route_id'   => 1,
                'latitude'   => 54.701631,
                'longitude'  => 20.521319,
                'created_at' => Carbon::create(2020, 3, 9, 15, 44, 40)
            ],
            [
                'car_id'     => 1,
                'route_id'   => 1,
                'latitude'   => 54.701613,
                'longitude'  => 20.521247,
                'created_at' => Carbon::create(2020, 3, 9, 15, 44, 45)
            ],
            [
                'car_id'     => 1,
                'route_id'   => 1,
                'latitude'   => 54.701510,
                'longitude'  => 20.521851,
                'created_at' => Carbon::create(2020, 3, 9, 15, 45, 30)
            ],
            [
                'car_id'     => 1,
                'route_id'   => 1,
                'latitude'   => 54.703277,
                'longitude'  => 20.523149,
                'created_at' => Carbon::create(2020, 3, 9, 15, 48, 10)
            ],
            [
                'car_id'     => 1,
                'route_id'   => 1,
                'latitude'   => 54.703408,
                'longitude'  => 20.523153,
                'created_at' => Carbon::create(2020, 3, 9, 15, 49, 0)
            ],
            [
                'car_id'     => 1,
                'route_id'   => 1,
                'latitude'   => 54.703524,
                'longitude'  => 20.523047,
                'created_at' => Carbon::create(2020, 3, 9, 15, 49, 15)
            ],
            [
                'car_id'     => 1,
                'route_id'   => 1,
                'latitude'   => 54.703663,
                'longitude'  => 20.522698,
                'created_at' => Carbon::create(2020, 3, 9, 15, 49, 55)
            ],
            [
                'car_id'     => 1,
                'route_id'   => 1,
                'latitude'   => 54.703913,
                'longitude'  => 20.521956,
                'created_at' => Carbon::create(2020, 3, 9, 15, 50, 40)
            ],
            [
                'car_id'     => 1,
                'route_id'   => 1,
                'latitude'   => 54.704383,
                'longitude'  => 20.519832,
                'created_at' => Carbon::create(2020, 3, 9, 15, 51, 40)
            ],
            [
                'car_id'     => 1,
                'route_id'   => 1,
                'latitude'   => 54.705159,
                'longitude'  => 20.516594,
                'created_at' => Carbon::create(2020, 3, 9, 15, 52, 40)
            ],
            [
                'car_id'     => 1,
                'route_id'   => 1,
                'latitude'   => 54.705194,
                'longitude'  => 20.516104,
                'created_at' => Carbon::create(2020, 3, 9, 15, 53, 0)
            ],
            [
                'car_id'     => 1,
                'route_id'   => 1,
                'latitude'   => 54.705021,
                'longitude'  => 20.515343,
                'created_at' => Carbon::create(2020, 3, 9, 15, 55, 0)
            ],
            [
                'car_id'     => 1,
                'route_id'   => 1,
                'latitude'   => 54.705797,
                'longitude'  => 20.514707,
                'created_at' => Carbon::create(2020, 3, 9, 15, 56, 10)
            ],
            [
                'car_id'     => 1,
                'route_id'   => 1,
                'latitude'   => 54.706528,
                'longitude'  => 20.514519,
                'created_at' => Carbon::create(2020, 3, 9, 15, 57, 10)
            ],
            [
                'car_id'     => 1,
                'route_id'   => 1,
                'latitude'   => 54.707593,
                'longitude'  => 20.514671,
                'created_at' => Carbon::create(2020, 3, 9, 15, 59, 10)
            ],
            [
                'car_id'     => 1,
                'route_id'   => 1,
                'latitude'   => 54.708188,
                'longitude'  => 20.514860,
                'created_at' => Carbon::create(2020, 3, 9, 16, 1, 5)
            ],
            [
                'car_id'     => 1,
                'route_id'   => 1,
                'latitude'   => 54.708520,
                'longitude'  => 20.515469,
                'created_at' => Carbon::create(2020, 3, 9, 16, 2, 5)
            ],
            [
                'car_id'     => 1,
                'route_id'   => 1,
                'latitude'   => 54.708956,
                'longitude'  => 20.521814,
                'created_at' => Carbon::create(2020, 3, 9, 16, 5, 5)
            ],
            [
                'car_id'     => 1,
                'route_id'   => 1,
                'latitude'   => 54.709319,
                'longitude'  => 20.526618,
                'created_at' => Carbon::create(2020, 3, 9, 16, 10, 5)
            ],
            [
                'car_id'     => 1,
                'route_id'   => 1,
                'latitude'   => 54.709785,
                'longitude'  => 20.531689,
                'created_at' => Carbon::create(2020, 3, 9, 16, 14, 5)
            ],
            [
                'car_id'     => 1,
                'route_id'   => 1,
                'latitude'   => 54.709691,
                'longitude'  => 20.537782,
                'created_at' => Carbon::create(2020, 3, 9, 16, 16, 35)
            ],
            [
                'car_id'     => 1,
                'route_id'   => 1,
                'latitude'   => 54.709667,
                'longitude'  => 20.549036,
                'created_at' => Carbon::create(2020, 3, 9, 16, 18, 55)
            ],
            [
                'car_id'     => 1,
                'route_id'   => 1,
                'latitude'   => 54.709027,
                'longitude'  => 20.560309,
                'created_at' => Carbon::create(2020, 3, 9, 16, 20, 14)
            ],
            [
                'car_id'     => 1,
                'route_id'   => 1,
                'latitude'   => 54.708626,
                'longitude'  => 20.569623,
                'created_at' => Carbon::create(2020, 3, 9, 16, 22, 14)
            ],
            [
                'car_id'     => 1,
                'route_id'   => 1,
                'latitude'   => 54.706817,
                'longitude'  => 20.584540,
                'created_at' => Carbon::create(2020, 3, 9, 16, 24, 14)
            ],
            [
                'car_id'     => 1,
                'route_id'   => 1,
                'latitude'   => 54.704787,
                'longitude'  => 20.591647,
                'created_at' => Carbon::create(2020, 3, 9, 16, 26, 56)
            ],
            [
                'car_id'     => 1,
                'route_id'   => 1,
                'latitude'   => 54.703855,
                'longitude'  => 20.600095,
                'created_at' => Carbon::create(2020, 3, 9, 16, 27, 10)
            ],
            [
                'car_id'     => 1,
                'route_id'   => 1,
                'latitude'   => 54.703402,
                'longitude'  => 20.600877,
                'created_at' => Carbon::create(2020, 3, 9, 16, 27, 20)
            ],
            [
                'car_id'     => 1,
                'route_id'   => 1,
                'latitude'   => 54.703039,
                'longitude'  => 20.600738,
                'created_at' => Carbon::create(2020, 3, 9, 16, 27, 30)
            ],
            [
                'car_id'     => 1,
                'route_id'   => 1,
                'latitude'   => 54.702571,
                'longitude'  => 20.599693,
                'created_at' => Carbon::create(2020, 3, 9, 16, 27, 39)
            ],
            [
                'car_id'     => 1,
                'route_id'   => 1,
                'latitude'   => 54.702649,
                'longitude'  => 20.598946,
                'created_at' => Carbon::create(2020, 3, 9, 16, 27, 50)
            ],
            [
                'car_id'     => 1,
                'route_id'   => 1,
                'latitude'   => 54.703174,
                'longitude'  => 20.598454,
                'created_at' => Carbon::create(2020, 3, 9, 16, 28, 5)
            ],
            [
                'car_id'     => 1,
                'route_id'   => 1,
                'latitude'   => 54.705646,
                'longitude'  => 20.598817,
                'created_at' => Carbon::create(2020, 3, 9, 16, 29, 15)
            ],
            [
                'car_id'     => 1,
                'route_id'   => 1,
                'latitude'   => 54.708404,
                'longitude'  => 20.598315,
                'created_at' => Carbon::create(2020, 3, 9, 16, 30, 15)
            ],
            [
                'car_id'     => 1,
                'route_id'   => 1,
                'latitude'   => 54.711678,
                'longitude'  => 20.597268,
                'created_at' => Carbon::create(2020, 3, 9, 16, 32, 15)
            ],
        ];
        $carPointsRoute1end    = [
            [
                'car_id'     => 1,
                'route_id'   => 1,
                'latitude'   => 54.713420,
                'longitude'  => 20.596984,
                'created_at' => Carbon::create(2020, 3, 9, 16, 32, 50)
            ],
            [
                'car_id'     => 1,
                'route_id'   => 1,
                'latitude'   => 54.714075,
                'longitude'  => 20.597000,
                'created_at' => Carbon::create(2020, 3, 9, 16, 33, 10)
            ],
            [
                'car_id'     => 1,
                'route_id'   => 1,
                'latitude'   => 54.714326,
                'longitude'  => 20.597353,
                'created_at' => Carbon::create(2020, 3, 9, 16, 33, 15)
            ],
            [
                'car_id'     => 1,
                'route_id'   => 1,
                'latitude'   => 54.714367,
                'longitude'  => 20.598400,
                'created_at' => Carbon::create(2020, 3, 9, 16, 33, 54)
            ],
            [
                'car_id'     => 1,
                'route_id'   => 1,
                'latitude'   => 54.713887,
                'longitude'  => 20.598286,
                'created_at' => Carbon::create(2020, 3, 9, 16, 34, 45)
            ],
            [
                'car_id'     => 1,
                'route_id'   => 1,
                'latitude'   => 54.713895,
                'longitude'  => 20.597888,
                'created_at' => Carbon::create(2020, 3, 9, 16, 35, 10)
            ],
        ];
        $carPointsRoute2       = [
            [
                'car_id'     => 1,
                'route_id'   => 2,
                'latitude'   => 61.037387,
                'longitude'  => 28.122967,
                'created_at' => Carbon::create(2020, 4, 7, 15, 43, 15),
            ],
            [
                'car_id'     => 1,
                'route_id'   => 2,
                'latitude'   => 61.038540,
                'longitude'  => 28.117018,
                'created_at' => Carbon::create(2020, 4, 7, 15, 44, 55),
            ],
            [
                'car_id'     => 1,
                'route_id'   => 2,
                'latitude'   => 61.039484,
                'longitude'  => 28.115207,
                'created_at' => Carbon::create(2020, 4, 7, 15, 44, 5),
            ],
            [
                'car_id'     => 1,
                'route_id'   => 2,
                'latitude'   => 61.039532,
                'longitude'  => 28.114243,
                'created_at' => Carbon::create(2020, 4, 7, 15, 45, 15),
            ],
            [
                'car_id'     => 1,
                'route_id'   => 2,
                'latitude'   => 61.039191,
                'longitude'  => 28.112966,
                'created_at' => Carbon::create(2020, 4, 7, 15, 47, 1),
            ],
            [
                'car_id'     => 1,
                'route_id'   => 2,
                'latitude'   => 61.038122,
                'longitude'  => 28.114292,
                'created_at' => Carbon::create(2020, 4, 7, 15, 48, 2),
            ],
            [
                'car_id'     => 1,
                'route_id'   => 2,
                'latitude'   => 61.033842,
                'longitude'  => 28.115219,
                'created_at' => Carbon::create(2020, 4, 7, 15, 49, 30),
            ],
            [
                'car_id'     => 1,
                'route_id'   => 2,
                'latitude'   => 61.033755,
                'longitude'  => 28.113877,
                'created_at' => Carbon::create(2020, 4, 7, 15, 50, 0),
            ],
            [
                'car_id'     => 1,
                'route_id'   => 2,
                'latitude'   => 61.033895,
                'longitude'  => 28.113128,
                'created_at' => Carbon::create(2020, 4, 7, 15, 50, 10),
            ],
            [
                'car_id'     => 1,
                'route_id'   => 2,
                'latitude'   => 61.034322,
                'longitude'  => 28.112754,
                'created_at' => Carbon::create(2020, 4, 7, 15, 50, 16),
            ],
            [
                'car_id'     => 1,
                'route_id'   => 2,
                'latitude'   => 61.034704,
                'longitude'  => 28.113347,
                'created_at' => Carbon::create(2020, 4, 7, 15, 50, 24),
            ],
            [
                'car_id'     => 1,
                'route_id'   => 2,
                'latitude'   => 61.035192,
                'longitude'  => 28.116248,
                'created_at' => Carbon::create(2020, 4, 7, 15, 50, 31),
            ],
            [
                'car_id'     => 1,
                'route_id'   => 2,
                'latitude'   => 61.036299,
                'longitude'  => 28.124400,
                'created_at' => Carbon::create(2020, 4, 7, 15, 53, 10),
            ],
        ];
        $carPointsRoute2end    = [
            [
                'car_id'     => 1,
                'route_id'   => 2,
                'latitude'   => 61.038739,
                'longitude'  => 28.139447,
                'created_at' => Carbon::create(2020, 4, 7, 15, 54, 15),
            ],
            [
                'car_id'     => 1,
                'route_id'   => 2,
                'latitude'   => 61.041175,
                'longitude'  => 28.160317,
                'created_at' => Carbon::create(2020, 4, 7, 15, 55, 10),
            ],
            [
                'car_id'     => 1,
                'route_id'   => 2,
                'latitude'   => 61.041821,
                'longitude'  => 28.170319,
                'created_at' => Carbon::create(2020, 4, 7, 15, 55, 57),
            ],
            [
                'car_id'     => 1,
                'route_id'   => 2,
                'latitude'   => 61.041342,
                'longitude'  => 28.184473,
                'created_at' => Carbon::create(2020, 4, 7, 15, 56, 10),
            ],
            [
                'car_id'     => 1,
                'route_id'   => 2,
                'latitude'   => 61.041555,
                'longitude'  => 28.189348,
                'created_at' => Carbon::create(2020, 4, 7, 15, 57, 0),
            ],
            [
                'car_id'     => 1,
                'route_id'   => 2,
                'latitude'   => 61.043871,
                'longitude'  => 28.205554,
                'created_at' => Carbon::create(2020, 4, 7, 15, 57, 50),
            ],
            [
                'car_id'     => 1,
                'route_id'   => 2,
                'latitude'   => 61.044081,
                'longitude'  => 28.208941,
                'created_at' => Carbon::create(2020, 4, 7, 15, 58, 12),
            ],
            [
                'car_id'     => 1,
                'route_id'   => 2,
                'latitude'   => 61.043909,
                'longitude'  => 28.212356,
                'created_at' => Carbon::create(2020, 4, 7, 15, 58, 30),
            ],
            [
                'car_id'     => 1,
                'route_id'   => 2,
                'latitude'   => 61.044231,
                'longitude'  => 28.213639,
                'created_at' => Carbon::create(2020, 4, 7, 15, 58, 59),
            ],
            [
                'car_id'     => 1,
                'route_id'   => 2,
                'latitude'   => 61.043094,
                'longitude'  => 28.217330,
                'created_at' => Carbon::create(2020, 4, 7, 15, 59, 50),
            ],
            [
                'car_id'     => 1,
                'route_id'   => 2,
                'latitude'   => 61.044617,
                'longitude'  => 28.221532,
                'created_at' => Carbon::create(2020, 4, 7, 16, 1, 5),
            ],
            [
                'car_id'     => 1,
                'route_id'   => 2,
                'latitude'   => 61.044109,
                'longitude'  => 28.222267,
                'created_at' => Carbon::create(2020, 4, 7, 16, 2, 30),
            ],
            [
                'car_id'     => 1,
                'route_id'   => 2,
                'latitude'   => 61.044242,
                'longitude'  => 28.221956,
                'created_at' => Carbon::create(2020, 4, 8, 16, 4, 10),
            ],
            [
                'car_id'     => 1,
                'route_id'   => 2,
                'latitude'   => 61.044028,
                'longitude'  => 28.222423,
                'created_at' => Carbon::create(2020, 4, 8, 16, 10, 10),
            ],
            [
                'car_id'     => 1,
                'route_id'   => 2,
                'latitude'   => 61.043362,
                'longitude'  => 28.222225,
                'created_at' => Carbon::create(2020, 4, 8, 16, 12, 12),
            ],
            [
                'car_id'     => 1,
                'route_id'   => 2,
                'latitude'   => 61.043085,
                'longitude'  => 28.221917,
                'created_at' => Carbon::create(2020, 4, 8, 16, 12, 50),
            ],
            [
                'car_id'     => 1,
                'route_id'   => 2,
                'latitude'   => 61.042059,
                'longitude'  => 28.220076,
                'created_at' => Carbon::create(2020, 4, 8, 16, 13, 40),
            ],
            [
                'car_id'     => 1,
                'route_id'   => 2,
                'latitude'   => 61.040918,
                'longitude'  => 28.222918,
                'created_at' => Carbon::create(2020, 4, 8, 16, 15, 0),
            ],
            [
                'car_id'     => 1,
                'route_id'   => 2,
                'latitude'   => 61.040810,
                'longitude'  => 28.222962,
                'created_at' => Carbon::create(2020, 4, 8, 16, 15, 10),
            ],
            [
                'car_id'     => 1,
                'route_id'   => 2,
                'latitude'   => 61.040742,
                'longitude'  => 28.223198,
                'created_at' => Carbon::create(2020, 4, 8, 16, 15, 20),
            ],
            [
                'car_id'     => 1,
                'route_id'   => 2,
                'latitude'   => 61.040636,
                'longitude'  => 28.223868,
                'created_at' => Carbon::create(2020, 4, 8, 16, 15, 20),
            ],
            [
                'car_id'     => 1,
                'route_id'   => 2,
                'latitude'   => 61.040006,
                'longitude'  => 28.226363,
                'created_at' => Carbon::create(2020, 4, 8, 16, 15, 59),
            ],
            [
                'car_id'     => 1,
                'route_id'   => 2,
                'latitude'   => 61.039468,
                'longitude'  => 28.228687,
                'created_at' => Carbon::create(2020, 4, 8, 16, 16, 30),
            ],
            [
                'car_id'     => 1,
                'route_id'   => 2,
                'latitude'   => 61.039365,
                'longitude'  => 28.228850,
                'created_at' => Carbon::create(2020, 4, 8, 16, 16, 40),
            ],
            [
                'car_id'     => 1,
                'route_id'   => 2,
                'latitude'   => 61.039363,
                'longitude'  => 28.229075,
                'created_at' => Carbon::create(2020, 4, 8, 16, 16, 50),
            ],
            [
                'car_id'     => 1,
                'route_id'   => 2,
                'latitude'   => 61.039282,
                'longitude'  => 28.229840,
                'created_at' => Carbon::create(2020, 4, 8, 16, 16, 59),
            ],
            [
                'car_id'     => 1,
                'route_id'   => 2,
                'latitude'   => 61.038680,
                'longitude'  => 28.232445,
                'created_at' => Carbon::create(2020, 4, 8, 16, 17, 20),
            ],
            [
                'car_id'     => 1,
                'route_id'   => 2,
                'latitude'   => 61.035395,
                'longitude'  => 28.242065,
                'created_at' => Carbon::create(2020, 4, 8, 16, 18, 20),
            ],
            [
                'car_id'     => 1,
                'route_id'   => 2,
                'latitude'   => 61.026474,
                'longitude'  => 28.256142,
                'created_at' => Carbon::create(2020, 4, 8, 16, 20, 20),
            ],
        ];
        $carSecondPointsRoute  = [
            [
                'car_id'     => 2,
                'route_id'   => 3,
                'latitude'   => 59.417472,
                'longitude'  => 24.796557,
                'created_at' => Carbon::create(2020, 4, 7, 1, 43, 15),
            ],
            [
                'car_id'     => 2,
                'route_id'   => 3,
                'latitude'   => 59.417156,
                'longitude'  => 24.796432,
                'created_at' => Carbon::create(2020, 4, 7, 1, 44, 0),
            ],
            [
                'car_id'     => 2,
                'route_id'   => 3,
                'latitude'   => 59.417120,
                'longitude'  => 24.796286,
                'created_at' => Carbon::create(2020, 4, 7, 1, 44, 10),
            ],
            [
                'car_id'     => 2,
                'route_id'   => 3,
                'latitude'   => 59.417146,
                'longitude'  => 24.795541,
                'created_at' => Carbon::create(2020, 4, 7, 1, 44, 40),
            ],
            [
                'car_id'     => 2,
                'route_id'   => 3,
                'latitude'   => 59.424074,
                'longitude'  => 24.785962,
                'created_at' => Carbon::create(2020, 4, 7, 1, 47, 4),
            ],
            [
                'car_id'     => 2,
                'route_id'   => 3,
                'latitude'   => 59.424478,
                'longitude'  => 24.785190,
                'created_at' => Carbon::create(2020, 4, 7, 1, 47, 50),
            ],
            [
                'car_id'     => 2,
                'route_id'   => 3,
                'latitude'   => 59.426166,
                'longitude'  => 24.780889,
                'created_at' => Carbon::create(2020, 4, 7, 1, 48, 40),
            ],
            [
                'car_id'     => 2,
                'route_id'   => 3,
                'latitude'   => 59.427456,
                'longitude'  => 24.778341,
                'created_at' => Carbon::create(2020, 4, 7, 1, 48, 59),
            ],
            [
                'car_id'     => 2,
                'route_id'   => 3,
                'latitude'   => 59.431438,
                'longitude'  => 24.768935,
                'created_at' => Carbon::create(2020, 4, 7, 1, 49, 5),
            ],
            [
                'car_id'     => 2,
                'route_id'   => 3,
                'latitude'   => 59.432659,
                'longitude'  => 24.763844,
                'created_at' => Carbon::create(2020, 4, 7, 1, 51, 5),
            ],
            [
                'car_id'     => 2,
                'route_id'   => 3,
                'latitude'   => 59.433181,
                'longitude'  => 24.763603,
                'created_at' => Carbon::create(2020, 4, 7, 1, 51, 30),
            ],
            [
                'car_id'     => 2,
                'route_id'   => 3,
                'latitude'   => 59.435332,
                'longitude'  => 24.764662,
                'created_at' => Carbon::create(2020, 4, 7, 1, 52, 10),
            ],
            [
                'car_id'     => 2,
                'route_id'   => 3,
                'latitude'   => 59.437446,
                'longitude'  => 24.764684,
                'created_at' => Carbon::create(2020, 4, 7, 1, 53, 20),
            ],
            [
                'car_id'     => 2,
                'route_id'   => 3,
                'latitude'   => 59.439973,
                'longitude'  => 24.763677,
                'created_at' => Carbon::create(2020, 4, 7, 1, 55, 40),
            ],
            [
                'car_id'     => 2,
                'route_id'   => 3,
                'latitude'   => 59.441610,
                'longitude'  => 24.764338,
                'created_at' => Carbon::create(2020, 4, 7, 1, 56, 30),
            ],
            [
                'car_id'     => 2,
                'route_id'   => 3,
                'latitude'   => 59.442441,
                'longitude'  => 24.763959,
                'created_at' => Carbon::create(2020, 4, 7, 1, 57, 30),
            ],
            [
                'car_id'     => 2,
                'route_id'   => 3,
                'latitude'   => 59.442900,
                'longitude'  => 24.766275,
                'created_at' => Carbon::create(2020, 4, 7, 1, 58, 20),
            ],
            [
                'car_id'     => 2,
                'route_id'   => 3,
                'latitude'   => 59.443225,
                'longitude'  => 24.767324,
                'created_at' => Carbon::create(2020, 4, 7, 14, 5, 45),
            ],
            [
                'car_id'     => 2,
                'route_id'   => 3,
                'latitude'   => 59.443539,
                'longitude'  => 24.769478,
                'created_at' => Carbon::create(2020, 4, 7, 14, 10, 45),
            ],
            [
                'car_id'     => 2,
                'route_id'   => 3,
                'latitude'   => 59.444025,
                'longitude'  => 24.770079,
                'created_at' => Carbon::create(2020, 4, 7, 15, 21, 30),
            ],
            [
                'car_id'     => 2,
                'route_id'   => 3,
                'latitude'   => 59.446860,
                'longitude'  => 24.773567,
                'created_at' => Carbon::create(2020, 4, 7, 15, 30, 30),
            ],
            [
                'car_id'     => 2,
                'route_id'   => 3,
                'latitude'   => 59.450426,
                'longitude'  => 24.780650,
                'created_at' => Carbon::create(2020, 4, 7, 15, 35, 32),
            ],
            [
                'car_id'     => 2,
                'route_id'   => 3,
                'latitude'   => 59.453153,
                'longitude'  => 24.781886,
                'created_at' => Carbon::create(2020, 4, 7, 15, 37, 32),
            ],
            [
                'car_id'     => 2,
                'route_id'   => 3,
                'latitude'   => 59.605537,
                'longitude'  => 24.687803,
                'created_at' => Carbon::create(2020, 4, 7, 16, 10, 54),
            ],
        ];
        $carSecondPointsRoute2 = [
            [
                'car_id'     => 2,
                'route_id'   => 4,
                'latitude'   => 63.990348,
                'longitude'  => -22.578255,
                'created_at' => now()->subMinutes(18)
            ],
            [
                'car_id'     => 2,
                'route_id'   => 4,
                'latitude'   => 63.993123,
                'longitude'  => -22.582289,
                'created_at' => now()->subMinutes(17)
            ],
            [
                'car_id'     => 2,
                'route_id'   => 4,
                'latitude'   => 63.995149,
                'longitude'  => -22.585484,
                'created_at' => now()->subMinutes(16)
            ],
            [
                'car_id'     => 2,
                'route_id'   => 4,
                'latitude'   => 63.997164,
                'longitude'  => -22.589354,
                'created_at' => now()->subMinutes(15)
            ],
            [
                'car_id'     => 2,
                'route_id'   => 4,
                'latitude'   => 64.000209,
                'longitude'  => -22.595405,
                'created_at' => now()->subMinutes(14)
            ],
            [
                'car_id'     => 2,
                'route_id'   => 4,
                'latitude'   => 64.001519,
                'longitude'  => -22.599440,
                'created_at' => now()->subMinutes(13)
            ],
            [
                'car_id'     => 2,
                'route_id'   => 4,
                'latitude'   => 64.002702,
                'longitude'  => -22.602115,
                'created_at' => now()->subMinutes(12)
            ],
            [
                'car_id'     => 2,
                'route_id'   => 4,
                'latitude'   => 64.002926,
                'longitude'  => -22.602033,
                'created_at' => now()->subMinutes(11)
            ],
            [
                'car_id'     => 2,
                'route_id'   => 4,
                'latitude'   => 64.002926,
                'longitude'  => -22.602033,
                'created_at' => now()->subMinutes(10)
            ],
            [
                'car_id'     => 2,
                'route_id'   => 4,
                'latitude'   => 64.003036,
                'longitude'  => -22.602546,
                'created_at' => now()->subMinutes(9)
            ],
            [
                'car_id'     => 2,
                'route_id'   => 4,
                'latitude'   => 64.002844,
                'longitude'  => -22.602865,
                'created_at' => now()->subMinutes(8)
            ],
            [
                'car_id'     => 2,
                'route_id'   => 4,
                'latitude'   => 64.002714,
                'longitude'  => -22.605127,
                'created_at' => now()->subMinutes(7)
            ],
            [
                'car_id'     => 2,
                'route_id'   => 4,
                'latitude'   => 64.002811,
                'longitude'  => -22.608130,
                'created_at' => now()->subMinutes(6)
            ],
            [
                'car_id'     => 2,
                'route_id'   => 4,
                'latitude'   => 64.002714,
                'longitude'  => -22.610853,
                'created_at' => now()->subMinutes(5)
            ],
            [
                'car_id'     => 2,
                'route_id'   => 4,
                'latitude'   => 64.000930,
                'longitude'  => -22.625468,
                'created_at' => now()->subMinutes(4)
            ],
        ];


        DB::table('cars_routes')->insert($carsRoutes);

        DB::table('cars_points')->insert($carPointsRoute2);         // 1 car
        DB::table('cars_points')->insert($carPointsRoute1);         // 1 car
        DB::table('cars_points')->insert($carSecondPointsRoute);    // 2 car
        DB::table('cars_points')->insert($carPointsRoute2end);      // 1 car
        DB::table('cars_points')->insert($carPointsRoute1end);      // 1 car
        DB::table('cars_points')->insert($carSecondPointsRoute2);   // 2 car


        foreach ($carsRoutes as $index => $value) {
            $points = CarRoute::find($index + 1)->points;

            foreach ($points as $index => $point) {
                if ($index + 1 <= $points->count() - 1) {
                    $startPoint = $points[$index];
                    $endPoint   = $points[$index + 1];

                    if ($startPoint->route_id === $endPoint->route_id) {
                        $interval = ($endPoint->created_at)->diffAsCarbonInterval($startPoint->created_at);
                        if ($startPoint->car_id === $endPoint->car_id) {
                            $section = CarRouteSection::create([
                                'route_id'       => $endPoint->route_id,
                                'moving_time_ru' => $interval->locale('ru')->forHumans(),
                                'moving_time_en' => $interval->locale('en')->forHumans(),
                            ]);
                        }
                        PointsSection::create([
                            'section_id' => $section->id,
                            'point_id'   => $startPoint->id
                        ]);
                        PointsSection::create([
                            'section_id' => $section->id,
                            'point_id'   => $endPoint->id
                        ]);
                    }
                }
            }
        }
    }
}
