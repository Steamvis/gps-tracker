<?php

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $checkboxes = [
            [
                'id'             => 1,
                'name'           => 'MAP_CAR_POINT_FROM_STORAGE',
                'translate_en'   => 'Replace point in map on uploaded photo',
                'translate_ru'   => 'Заменить метку на карте на загруженную фотографию',
                'type'           => Setting::TYPE_CHECKBOX,
                'value_variants' => '0,1',
            ],
        ];

        $selects = [
            [
                'id'             => 2,
                'name'           => 'CAR_DATATABLE_PAGINATE',
                'translate_en'   => 'The number of entries in the table',
                'translate_ru'   => 'Количество записей в таблице',
                'type'           => Setting::TYPE_SELECT,
                'value_variants' => '10,20,50,100',
            ],
        ];

        DB::table('settings')->insert($checkboxes);
        DB::table('settings')->insert($selects);
    }
}
