<?php

use Illuminate\Database\Seeder;

class CarMarkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            // RUSSIA
            [
                'name'            => 'Камаз',
                'country_id'      => '24',
                'mark_image_path' => 'img/marks/kamaz.png'
            ],
            [
                'name'            => 'Газ',
                'country_id'      => '24',
                'mark_image_path' => 'img/marks/gaz.png'
            ],
            [
                'name'            => 'Урал',
                'country_id'      => '24',
                'mark_image_path' => 'img/marks/ural.png'
            ],
            [
                'name'            => 'УАЗ',
                'country_id'      => '24',
                'mark_image_path' => 'img/marks/uaz.png'
            ],
            // UKRAINE
            [
                'name'            => 'Краз',
                'country_id'      => '17',
                'mark_image_path' => 'img/marks/kraz.png'

            ],
            // BELARUS
            [
                'name'            => 'Белаз',
                'country_id'      => '48',
                'mark_image_path' => 'img/marks/belaz.png'
            ],
            [
                'name'            => 'МАЗ',
                'country_id'      => '48',
                'mark_image_path' => 'img/marks/maz.png'
            ],
            // NEDERLANDS
            [
                'name'            => 'DAF',
                'country_id'      => '7',
                'mark_image_path' => 'img/marks/daf.png'
            ],
            // CHINE
            [
                'name'            => 'Dongfeng',
                'country_id'      => '36',
                'mark_image_path' => 'img/marks/dongfeng.png'
            ],
            [
                'name'            => 'FAW',
                'country_id'      => '36',
                'mark_image_path' => 'img/marks/faw.png'
            ],
            [
                'name'            => 'Foton',
                'country_id'      => '36',
                'mark_image_path' => 'img/marks/foton.png'
            ],
            // USA
            [
                'name'            => 'Ford',
                'country_id'      => '12',
                'mark_image_path' => 'img/marks/ford.png'
            ],
            [
                'name'            => 'Mack',
                'country_id'      => '12',
                'mark_image_path' => 'img/marks/mack.png'
            ],
            [
                'name'            => 'Freightliner',
                'country_id'      => '12',
                'mark_image_path' => 'img/marks/freightliner.png'
            ],
            [
                'name'            => 'International',
                'country_id'      => '12',
                'mark_image_path' => 'img/marks/international.png'
            ],
            [
                'name'            => 'Kenworth',
                'country_id'      => '12',
                'mark_image_path' => 'img/marks/kenworth.png'
            ],
            [
                'name'            => 'Tesla',
                'country_id'      => '12',
                'mark_image_path' => 'img/marks/tesla.png'
            ],
            [
                'name'            => 'Peterbilt',
                'country_id'      => '12',
                'mark_image_path' => 'img/marks/peterbilt.png'
            ],
            // JAPAN
            [
                'name'            => 'Isuzu',
                'country_id'      => '41',
                'mark_image_path' => 'img/marks/isuzu.png'
            ],
            // ITALY
            [
                'name'            => 'Iveco',
                'country_id'      => '15',
                'mark_image_path' => 'img/marks/iveco.png'
            ],
            // GERMANY
            [
                'name'            => 'MAN',
                'country_id'      => '44',
                'mark_image_path' => 'img/marks/man.png'
            ],
            [
                'name'            => 'Mercedes-Benz',
                'country_id'      => '44',
                'mark_image_path' => 'img/marks/mercedes.png'
            ],
            // FRANCE
            [
                'name'            => 'Renault',
                'country_id'      => '2',
                'mark_image_path' => 'img/marks/renault.png'
            ],
            // SWEDEN
            [
                'name'            => 'Scania',
                'country_id'      => '27',
                'mark_image_path' => 'img/marks/scania.png'
            ],
            [
                'name'            => 'Volvo',
                'country_id'      => '27',
                'mark_image_path' => 'img/marks/volvo.png'
            ],
            // FINLAND
            [
                'name'            => 'SISU',
                'country_id'      => '22',
                'mark_image_path' => 'img/marks/sisu.png'
            ]
        ];
        DB::table('car_marks')->insert($data);
    }
}
