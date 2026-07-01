<?php

use Illuminate\Database\Seeder;

class CarCharacteristicsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                'id'      => 1,
                'name_ru' => 'Тип грузовой техники',
                'name_en' => 'Type of trucks'
            ],
            [
                'id'      => 2,
                'name_ru' => 'Колесная формула',
                'name_en' => 'Wheel Formula'
            ],
            [
                'id'      => 3,
                'name_ru' => 'Мощность двигателя',
                'name_en' => 'Engine power'
            ],
            [
                'id'      => 4,
                'name_ru' => 'Высота',
                'name_en' => 'Height'
            ],
            [
                'id'      => 5,
                'name_ru' => 'Ширина',
                'name_en' => 'Weight'
            ],
            [
                'id'      => 6,
                'name_ru' => 'Грузоподъемность',
                'name_en' => 'Carrying capacity'
            ],
        ];
        DB::table('car_characteristics')->insert($data);
    }
}
