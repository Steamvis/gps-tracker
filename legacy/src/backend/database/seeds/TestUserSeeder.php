<?php

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;

class TestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = [
            'first_name'        => 'Test',
            'last_name'         => 'TestUser',
            'gender'            => 'male',
            'locale'            => 'ru',
            'level'             => User::LEVEL_COMPANY_OWNER,
            'email'             => 'test@mail.ru',
            'password'          => Hash::make('testPassword'),
            'email_verified_at' => now(),
        ];

        $userFake = [
            'first_name'        => 'Test2',
            'last_name'         => 'TestUser2',
            'gender'            => 'male',
            'locale'            => 'ru',
            'level'             => User::LEVEL_COMPANY_OWNER,
            'email'             => 'test2@mail.ru',
            'password'          => Hash::make('testPassword'),
            'email_verified_at' => now(),
        ];
        DB::table('users')->insert($user);
        DB::table('users')->insert($userFake);

        $company = [
            'owner_id'      => 1,
            'country_id'    => 24,
            'cars_counter'  => 3,
            'staff_counter' => 1,
            'title'         => 'TestCompany',
        ];
        DB::table('companies')->insert($company);
        User::find(1)->update(['company_id' => 1]);

        DB::table('users_settings')->insert([
            [
                'id'         => 1,
                'user_id'    => 1,
                'setting_id' => 1,
                'value'      => 1,
            ],
            [
                'id'         => 2,
                'user_id'    => 1,
                'setting_id' => 2,
                'value'      => 10,
            ],
        ]);

        $cars = [
            [
                'name'       => 'TGX',
                'year'       => 2020,
                'mark_id'    => 22,
                'company_id' => 1,
                'color'      => '#2f80ed',
                'vin_number' => 'WDDNF9EB8DA526479',
                'gov_number' => 'X999XE777',
                'api_code'   => sha1(time() . env('APP_KEY') . 152),
            ],
        ];
        DB::table('cars')->insert($cars);

        Company::updateCarsCounter(User::find(1)->company);
    }
}
