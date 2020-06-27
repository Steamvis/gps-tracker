<?php

use Illuminate\Database\Seeder;

class CountriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [// 1
                'name_ru'   => 'Гонконг',
                'name_en'   => 'Hong Kong',
                'code'      => 'HK',
                'flag_path' => 'img/flags/HK.png'
            ],
            [// 2
                'name_ru'   => 'Франция',
                'name_en'   => 'France',
                'code'      => 'FR',
                'flag_path' => 'img/flags/FR.png'
            ],
            [// 3
                'name_ru'   => 'Мексика',
                'name_en'   => 'Mexico',
                'code'      => 'MX',
                'flag_path' => 'img/flags/MX.png'
            ],
            [// 4
                'name_ru'   => 'Египет',
                'name_en'   => 'Egypt',
                'code'      => 'EG',
                'flag_path' => 'img/flags/EG.png'
            ],
            [// 5
                'name_ru'   => 'Тайланд',
                'name_en'   => 'Thailand',
                'code'      => 'TH',
                'flag_path' => 'img/flags/TH.png'
            ],
            [// 6
                'name_ru'   => 'Канада',
                'name_en'   => 'Canada',
                'code'      => 'CA',
                'flag_path' => 'img/flags/CA.png'
            ],
            [// 7
                'name_ru'   => 'Нидерланды',
                'name_en'   => 'Netherlands',
                'code'      => 'NL',
                'flag_path' => 'img/flags/NL.png'
            ],
            [// 8
                'name_ru'   => 'Испания',
                'name_en'   => 'Spain',
                'code'      => 'ES',
                'flag_path' => 'img/flags/ES.png'
            ],
            [// 9
                'name_ru'   => 'Дания',
                'name_en'   => 'Denmark',
                'code'      => 'DK',
                'flag_path' => 'img/flags/DK.png'
            ],
            [// 10
                'name_ru'   => 'Швейцария',
                'name_en'   => 'Switzerland',
                'code'      => 'CH',
                'flag_path' => 'img/flags/CH.png'
            ],
            [// 11
                'name_ru'   => 'Португалия',
                'name_en'   => 'Portugal',
                'code'      => 'PT',
                'flag_path' => 'img/flags/PT.png'
            ],
            [// 12
                'name_ru'   => 'USA',
                'name_en'   => 'USA',
                'code'      => 'US',
                'flag_path' => 'img/flags/US.png'
            ],
            [// 13
                'name_ru'   => 'Перу',
                'name_en'   => 'Peru',
                'code'      => 'PE',
                'flag_path' => 'img/flags/PE.png'
            ],
            [// 14
                'name_ru'   => 'Австралия',
                'name_en'   => 'Australia',
                'code'      => 'AU',
                'flag_path' => 'img/flags/AU.png'
            ],
            [// 15
                'name_ru'   => 'Италия',
                'name_en'   => 'Italy',
                'code'      => 'IT',
                'flag_path' => 'img/flags/IT.png'
            ],
            [// 16
                'name_ru'   => 'Чили',
                'name_en'   => 'Chile',
                'code'      => 'CL',
                'flag_path' => 'img/flags/CL.png'
            ],
            [// 17
                'name_ru'   => 'Украина',
                'name_en'   => 'Ukraine',
                'code'      => 'UA',
                'flag_path' => 'img/flags/UA.png'
            ],
            [// 18
                'name_ru'   => 'Саудовская Аравия',
                'name_en'   => 'Saudi Arabia',
                'code'      => 'SA',
                'flag_path' => 'img/flags/SA.png'
            ],
            [// 19
                'name_ru'   => 'Сингапур',
                'name_en'   => 'Singapore',
                'code'      => 'SG',
                'flag_path' => 'img/flags/SG.png'
            ],
            [// 20
                'name_ru'   => 'Бразилия',
                'name_en'   => 'Brazil',
                'code'      => 'BR',
                'flag_path' => 'img/flags/BR.png'
            ],
            [// 21
                'name_ru'   => 'Норвегия',
                'name_en'   => 'Norway',
                'code'      => 'NO',
                'flag_path' => 'img/flags/NO.png'
            ],
            [// 22
                'name_ru'   => 'Финляндия',
                'name_en'   => 'Finland',
                'code'      => 'FI',
                'flag_path' => 'img/flags/FI.png'
            ],
            [// 22
                'name_ru'   => 'Польша',
                'name_en'   => 'Poland',
                'code'      => 'PL',
                'flag_path' => 'img/flags/PL.png'
            ],
            [// 23
                'name_ru'   => 'Греция',
                'name_en'   => 'Greece',
                'code'      => 'GR',
                'flag_path' => 'img/flags/GR.png'
            ],
            [// 24
             'name_ru'   => 'Россия',
             'name_en'   => 'Russia',
                'code'      => 'RU',
                'flag_path' => 'img/flags/RU.png'
            ],
            [// 25
                'name_ru'   => 'Чехия',
                'name_en'   => 'Czechia',
                'code'      => 'CZ',
                'flag_path' => 'img/flags/CZ.png'
            ],
            [// 26
                'name_ru'   => 'Венесуэла',
                'name_en'   => 'Venezuela',
                'code'      => 'VE',
                'flag_path' => 'img/flags/VE.png'
            ],
            [// 27
                'name_ru'   => 'Швеция',
                'name_en'   => 'Sweden',
                'code'      => 'SE',
                'flag_path' => 'img/flags/SE.png'
            ],
            [// 28
                'name_ru'   => 'Великобритания',
                'name_en'   => 'United Kingdom',
                'code'      => 'GB',
                'flag_path' => 'img/flags/GB.png'
            ],
            [// 29
                'name_ru'   => 'Индонезия',
                'name_en'   => 'Indonesia',
                'code'      => 'ID',
                'flag_path' => 'img/flags/ID.png'
            ],
            [// 30
                'name_ru'   => 'ЮАР',
                'name_en'   => 'South Africa',
                'code'      => 'ZA',
                'flag_path' => 'img/flags/ZA.png'
            ],
            [// 31
                'name_ru'   => 'Израиль',
                'name_en'   => 'Israel',
                'code'      => 'IL',
                'flag_path' => 'img/flags/IL.png'
            ],
            [// 32
                'name_ru'   => 'Тайвань',
                'name_en'   => 'Taiwan',
                'code'      => 'TW',
                'flag_path' => 'img/flags/TW.png'
            ],
            [// 33
                'name_ru'   => 'Южная Корея',
                'name_en'   => 'South Korea',
                'code'      => 'KR',
                'flag_path' => 'img/flags/KR.png'
            ],
            [// 34
                'name_ru'   => 'Филиппины',
                'name_en'   => 'Philippines',
                'code'      => 'PH',
                'flag_path' => 'img/flags/PH.png'
            ],
            [// 35
                'name_ru'   => 'Колумбия',
                'name_en'   => 'Colombia',
                'code'      => 'CO',
                'flag_path' => 'img/flags/CO.png'
            ],
            [// 36
                'name_ru'   => 'Китай',
                'name_en'   => 'China',
                'code'      => 'CN',
                'flag_path' => 'img/flags/CN.png'
            ],
            [// 37
                'name_ru'   => 'Индия',
                'name_en'   => 'India',
                'code'      => 'IN',
                'flag_path' => 'img/flags/IN.png'
            ],
            [// 38
                'name_ru'   => 'Турция',
                'name_en'   => 'Turkey',
                'code'      => 'TR',
                'flag_path' => 'img/flags/TR.png'
            ],
            [// 39
                'name_ru'   => 'Марокко',
                'name_en'   => 'Morocco',
                'code'      => 'MA',
                'flag_path' => 'img/flags/MA.png'
            ],
            [// 40
                'name_ru'   => 'Бельгия',
                'name_en'   => 'Belgium',
                'code'      => 'BE',
                'flag_path' => 'img/flags/BE.png'
            ],
            [// 41
                'name_ru'   => 'Япония',
                'name_en'   => 'Japan',
                'code'      => 'JP',
                'flag_path' => 'img/flags/JP.png'
            ],
            [// 42
                'name_ru'   => 'Аргентина',
                'name_en'   => 'Argentina',
                'code'      => 'AR',
                'flag_path' => 'img/flags/AR.png'
            ],
            [// 43
                'name_ru'   => 'Иран',
                'name_en'   => 'Iran',
                'code'      => 'IR',
                'flag_path' => 'img/flags/IR.png'
            ],
            [// 44
                'name_ru'   => 'Германия',
                'name_en'   => 'Germany',
                'code'      => 'DE',
                'flag_path' => 'img/flags/DE.png'
            ],
            [// 45
                'name_ru'   => 'Ирландия',
                'name_en'   => 'Ireland',
                'code'      => 'IE',
                'flag_path' => 'img/flags/IE.png'
            ],
            [// 46
                'name_ru'   => 'Вьетнам',
                'name_en'   => 'Vietnam',
                'code'      => 'VN',
                'flag_path' => 'img/flags/VN.png'
            ],
            [// 47
                'name_ru'   => 'ОАЭ',
                'name_en'   => 'United Arab Emirates',
                'code'      => 'AE',
                'flag_path' => 'img/flags/AE.png'
            ],
            [// 48
                'name_ru'   => 'Беларусь',
                'name_en'   => 'Belarus',
                'code'      => 'BY',
                'flag_path' => 'img/flags/AE.png'
            ],
        ];
        DB::table('countries')->insert($data);
    }
}
