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
                'flag_path' => 'images/flags/HK.png'
            ],
            [// 2
                'name_ru'   => 'Франция',
                'name_en'   => 'France',
                'code'      => 'FR',
                'flag_path' => 'images/flags/FR.png'
            ],
            [// 3
                'name_ru'   => 'Мексика',
                'name_en'   => 'Mexico',
                'code'      => 'MX',
                'flag_path' => 'images/flags/MX.png'
            ],
            [// 4
                'name_ru'   => 'Египет',
                'name_en'   => 'Egypt',
                'code'      => 'EG',
                'flag_path' => 'images/flags/EG.png'
            ],
            [// 5
                'name_ru'   => 'Тайланд',
                'name_en'   => 'Thailand',
                'code'      => 'TH',
                'flag_path' => 'images/flags/TH.png'
            ],
            [// 6
                'name_ru'   => 'Канада',
                'name_en'   => 'Canada',
                'code'      => 'CA',
                'flag_path' => 'images/flags/CA.png'
            ],
            [// 7
                'name_ru'   => 'Нидерланды',
                'name_en'   => 'Netherlands',
                'code'      => 'NL',
                'flag_path' => 'images/flags/NL.png'
            ],
            [// 8
                'name_ru'   => 'Испания',
                'name_en'   => 'Spain',
                'code'      => 'ES',
                'flag_path' => 'images/flags/ES.png'
            ],
            [// 9
                'name_ru'   => 'Дания',
                'name_en'   => 'Denmark',
                'code'      => 'DK',
                'flag_path' => 'images/flags/DK.png'
            ],
            [// 10
                'name_ru'   => 'Швейцария',
                'name_en'   => 'Switzerland',
                'code'      => 'CH',
                'flag_path' => 'images/flags/CH.png'
            ],
            [// 11
                'name_ru'   => 'Португалия',
                'name_en'   => 'Portugal',
                'code'      => 'PT',
                'flag_path' => 'images/flags/PT.png'
            ],
            [// 12
                'name_ru'   => 'USA',
                'name_en'   => 'USA',
                'code'      => 'US',
                'flag_path' => 'images/flags/US.png'
            ],
            [// 13
                'name_ru'   => 'Перу',
                'name_en'   => 'Peru',
                'code'      => 'PE',
                'flag_path' => 'images/flags/PE.png'
            ],
            [// 14
                'name_ru'   => 'Австралия',
                'name_en'   => 'Australia',
                'code'      => 'AU',
                'flag_path' => 'images/flags/AU.png'
            ],
            [// 15
                'name_ru'   => 'Италия',
                'name_en'   => 'Italy',
                'code'      => 'IT',
                'flag_path' => 'images/flags/IT.png'
            ],
            [// 16
                'name_ru'   => 'Чили',
                'name_en'   => 'Chile',
                'code'      => 'CL',
                'flag_path' => 'images/flags/CL.png'
            ],
            [// 17
                'name_ru'   => 'Украина',
                'name_en'   => 'Ukraine',
                'code'      => 'UA',
                'flag_path' => 'images/flags/UA.png'
            ],
            [// 18
                'name_ru'   => 'Саудовская Аравия',
                'name_en'   => 'Saudi Arabia',
                'code'      => 'SA',
                'flag_path' => 'images/flags/SA.png'
            ],
            [// 19
                'name_ru'   => 'Сингапур',
                'name_en'   => 'Singapore',
                'code'      => 'SG',
                'flag_path' => 'images/flags/SG.png'
            ],
            [// 20
                'name_ru'   => 'Бразилия',
                'name_en'   => 'Brazil',
                'code'      => 'BR',
                'flag_path' => 'images/flags/BR.png'
            ],
            [// 21
                'name_ru'   => 'Норвегия',
                'name_en'   => 'Norway',
                'code'      => 'NO',
                'flag_path' => 'images/flags/NO.png'
            ],
            [// 22
                'name_ru'   => 'Финляндия',
                'name_en'   => 'Finland',
                'code'      => 'FI',
                'flag_path' => 'images/flags/FI.png'
            ],
            [// 23
                'name_ru'   => 'Польша',
                'name_en'   => 'Poland',
                'code'      => 'PL',
                'flag_path' => 'images/flags/PL.png'
            ],
            [// 24
                'name_ru'   => 'Греция',
                'name_en'   => 'Greece',
                'code'      => 'GR',
                'flag_path' => 'images/flags/GR.png'
            ],
            [// 25
             'name_ru'   => 'Россия',
             'name_en'   => 'Russia',
                'code'      => 'RU',
                'flag_path' => 'images/flags/RU.png'
            ],
            [// 26
                'name_ru'   => 'Чехия',
                'name_en'   => 'Czechia',
                'code'      => 'CZ',
                'flag_path' => 'images/flags/CZ.png'
            ],
            [// 27
                'name_ru'   => 'Венесуэла',
                'name_en'   => 'Venezuela',
                'code'      => 'VE',
                'flag_path' => 'images/flags/VE.png'
            ],
            [// 28
                'name_ru'   => 'Швеция',
                'name_en'   => 'Sweden',
                'code'      => 'SE',
                'flag_path' => 'images/flags/SE.png'
            ],
            [// 29
                'name_ru'   => 'Великобритания',
                'name_en'   => 'United Kingdom',
                'code'      => 'GB',
                'flag_path' => 'images/flags/GB.png'
            ],
            [// 30
                'name_ru'   => 'Индонезия',
                'name_en'   => 'Indonesia',
                'code'      => 'ID',
                'flag_path' => 'images/flags/ID.png'
            ],
            [// 31
                'name_ru'   => 'ЮАР',
                'name_en'   => 'South Africa',
                'code'      => 'ZA',
                'flag_path' => 'images/flags/ZA.png'
            ],
            [// 32
                'name_ru'   => 'Израиль',
                'name_en'   => 'Israel',
                'code'      => 'IL',
                'flag_path' => 'images/flags/IL.png'
            ],
            [// 33
                'name_ru'   => 'Тайвань',
                'name_en'   => 'Taiwan',
                'code'      => 'TW',
                'flag_path' => 'images/flags/TW.png'
            ],
            [// 34
                'name_ru'   => 'Южная Корея',
                'name_en'   => 'South Korea',
                'code'      => 'KR',
                'flag_path' => 'images/flags/KR.png'
            ],
            [// 35
                'name_ru'   => 'Филиппины',
                'name_en'   => 'Philippines',
                'code'      => 'PH',
                'flag_path' => 'images/flags/PH.png'
            ],
            [// 36
                'name_ru'   => 'Колумбия',
                'name_en'   => 'Colombia',
                'code'      => 'CO',
                'flag_path' => 'images/flags/CO.png'
            ],
            [// 37
                'name_ru'   => 'Китай',
                'name_en'   => 'China',
                'code'      => 'CN',
                'flag_path' => 'images/flags/CN.png'
            ],
            [// 38
                'name_ru'   => 'Индия',
                'name_en'   => 'India',
                'code'      => 'IN',
                'flag_path' => 'images/flags/IN.png'
            ],
            [// 39
                'name_ru'   => 'Турция',
                'name_en'   => 'Turkey',
                'code'      => 'TR',
                'flag_path' => 'images/flags/TR.png'
            ],
            [// 40
                'name_ru'   => 'Марокко',
                'name_en'   => 'Morocco',
                'code'      => 'MA',
                'flag_path' => 'images/flags/MA.png'
            ],
            [// 41
                'name_ru'   => 'Бельгия',
                'name_en'   => 'Belgium',
                'code'      => 'BE',
                'flag_path' => 'images/flags/BE.png'
            ],
            [// 42
                'name_ru'   => 'Япония',
                'name_en'   => 'Japan',
                'code'      => 'JP',
                'flag_path' => 'images/flags/JP.png'
            ],
            [// 43
                'name_ru'   => 'Аргентина',
                'name_en'   => 'Argentina',
                'code'      => 'AR',
                'flag_path' => 'images/flags/AR.png'
            ],
            [// 44
                'name_ru'   => 'Иран',
                'name_en'   => 'Iran',
                'code'      => 'IR',
                'flag_path' => 'images/flags/IR.png'
            ],
            [// 45
                'name_ru'   => 'Германия',
                'name_en'   => 'Germany',
                'code'      => 'DE',
                'flag_path' => 'images/flags/DE.png'
            ],
            [// 46
                'name_ru'   => 'Ирландия',
                'name_en'   => 'Ireland',
                'code'      => 'IE',
                'flag_path' => 'images/flags/IE.png'
            ],
            [// 47
                'name_ru'   => 'Вьетнам',
                'name_en'   => 'Vietnam',
                'code'      => 'VN',
                'flag_path' => 'images/flags/VN.png'
            ],
            [// 48
                'name_ru'   => 'ОАЭ',
                'name_en'   => 'United Arab Emirates',
                'code'      => 'AE',
                'flag_path' => 'images/flags/AE.png'
            ],
            [// 49
                'name_ru'   => 'Беларусь',
                'name_en'   => 'Belarus',
                'code'      => 'BY',
                'flag_path' => 'images/flags/AE.png'
            ],
        ];
        DB::table('countries')->insert($data);
    }
}
