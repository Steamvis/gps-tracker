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
            [
                'name'            => 'Other',
                'country_id'      => '1',
                'mark_image_path' => 'images/marks/other.png'
            ],
            // RUSSIA
            [
                'name'            => 'Камаз',
                'country_id'      => '25',
                'mark_image_path' => 'images/marks/kamaz.png'
            ],
            [
                'name'            => 'Газ',
                'country_id'      => '25',
                'mark_image_path' => 'images/marks/gaz.png'
            ],
            [
                'name'            => 'Урал',
                'country_id'      => '25',
                'mark_image_path' => 'images/marks/ural.png'
            ],
            [
                'name'            => 'УАЗ',
                'country_id'      => '25',
                'mark_image_path' => 'images/marks/uaz.png'
            ],
            // UKRAINE
            [
                'name'            => 'Краз',
                'country_id'      => '17',
                'mark_image_path' => 'images/marks/kraz.png'

            ],
            // BELARUS
            [
                'name'            => 'Белаз',
                'country_id'      => '49',
                'mark_image_path' => 'images/marks/belaz.png'
            ],
            [
                'name'            => 'МАЗ',
                'country_id'      => '49',
                'mark_image_path' => 'images/marks/maz.png'
            ],
            // NEDERLANDS
            [
                'name'            => 'DAF',
                'country_id'      => '7',
                'mark_image_path' => 'images/marks/daf.png'
            ],
            // CHINE
            [
                'name'            => 'Dongfeng',
                'country_id'      => '37',
                'mark_image_path' => 'images/marks/dongfeng.png'
            ],
            [
                'name'            => 'FAW',
                'country_id'      => '37',
                'mark_image_path' => 'images/marks/faw.png'
            ],
            [
                'name'            => 'Foton',
                'country_id'      => '37',
                'mark_image_path' => 'images/marks/foton.png'
            ],
            // USA
            [
                'name'            => 'Ford',
                'country_id'      => '12',
                'mark_image_path' => 'images/marks/ford.png'
            ],
            [
                'name'            => 'Mack',
                'country_id'      => '12',
                'mark_image_path' => 'images/marks/mack.png'
            ],
            [
                'name'            => 'Freightliner',
                'country_id'      => '12',
                'mark_image_path' => 'images/marks/freightliner.png'
            ],
            [
                'name'            => 'International',
                'country_id'      => '12',
                'mark_image_path' => 'images/marks/international.png'
            ],
            [
                'name'            => 'Kenworth',
                'country_id'      => '12',
                'mark_image_path' => 'images/marks/kenworth.png'
            ],
            [
                'name'            => 'Tesla',
                'country_id'      => '12',
                'mark_image_path' => 'images/marks/tesla.png'
            ],
            [
                'name'            => 'Peterbilt',
                'country_id'      => '12',
                'mark_image_path' => 'images/marks/peterbilt.png'
            ],
            // JAPAN
            [
                'name'            => 'Isuzu',
                'country_id'      => '42',
                'mark_image_path' => 'images/marks/isuzu.png'
            ],
            // ITALY
            [
                'name'            => 'Iveco',
                'country_id'      => '15',
                'mark_image_path' => 'images/marks/iveco.png'
            ],
            // GERMANY
            [
                'name'            => 'MAN',
                'country_id'      => '45',
                'mark_image_path' => 'images/marks/man.png'
            ],
            [
                'name'            => 'Mercedes-Benz',
                'country_id'      => '45',
                'mark_image_path' => 'images/marks/mercedes.png'
            ],
            // FRANCE
            [
                'name'            => 'Renault',
                'country_id'      => '2',
                'mark_image_path' => 'images/marks/renault.png'
            ],
            // SWEDEN
            [
                'name'            => 'Scania',
                'country_id'      => '28',
                'mark_image_path' => 'images/marks/scania.png'
            ],
            [
                'name'            => 'Volvo',
                'country_id'      => '28',
                'mark_image_path' => 'images/marks/volvo.png'
            ],
            // FINLAND
            [
                'name'            => 'SISU',
                'country_id'      => '22',
                'mark_image_path' => 'images/marks/sisu.png'
            ]
        ];
        DB::table('car_marks')->insert($data);
    }
}
