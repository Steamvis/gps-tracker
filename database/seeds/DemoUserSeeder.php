<?php

use App\Models\Car\CarRoute;
use App\Models\Car\CarRouteSection;
use App\Models\Car\CarPoint;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

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
            'cars_counter'  => 2,
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
                'gov_number' => 'X9999XE777',
                'api_code'   => Str::limit(sha1(time() . env('APP_KEY') . 1), 10, ''),
            ],
            [
                'name'       => 'TGX',
                'year'       => 2020,
                'mark_id'    => 22,
                'company_id' => 1,
                'color'      => '#2f80ed',
                'vin_number' => 'XUUYA755JF000015E',
                'gov_number' => 'A4567OH777',
                'api_code'   => Str::limit(sha1(time() . env('APP_KEY') . 2), 10, ''),
            ],
        ];
        DB::table('cars')->insert($cars);

        $carsRoutes          = [
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
                'end_time'   => Carbon::create(2020, 4, 8, 16, 4, 10),
            ]
        ];
        $carsPointsRoute1    = [
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
        $carsPointsRoute1end = [
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
        $carsPointsRoute2    = [
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
        $carsPointsRoute2end = [
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
        ];


        DB::table('cars_routes')->insert($carsRoutes);
        DB::table('cars_points')->insert($carsPointsRoute2);
        DB::table('cars_points')->insert($carsPointsRoute1);
        DB::table('cars_points')->insert($carsPointsRoute2end);
        DB::table('cars_points')->insert($carsPointsRoute1end);


        foreach ($carsRoutes as $index => $value) {
            $points = CarRoute::find($index + 1)->points;

            foreach ($points as $index => $point) {
                if ($index + 1 <= $points->count() - 1) {
                    $startPoint = $points[$index];
                    $endPoint   = $points[$index + 1];

                    if ($startPoint->route_id === $endPoint->route_id) {
                        $interval = ($endPoint->created_at)->diffAsCarbonInterval($startPoint->created_at);
                        if ($startPoint->car_id === $endPoint->car_id) {
                            CarRouteSection::create([
                                'route_id'       => $endPoint->route_id,
                                'start_point_id' => $startPoint->id,
                                'end_point_id'   => $endPoint->id,
                                'moving_time_ru' => $interval->locale('ru')->forHumans(),
                                'moving_time_en' => $interval->locale('en')->forHumans(),
                            ]);
                        }
                    }
                }
            }
        }
    }
}
