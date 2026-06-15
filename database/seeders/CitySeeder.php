<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domains\Catalog\Models\City;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        $cities = [
            ['name' => 'Москва', 'region' => 'Москва'],
            ['name' => 'Санкт-Петербург', 'region' => 'Санкт-Петербург'],
            ['name' => 'Новосибирск', 'region' => 'Новосибирская область'],
            ['name' => 'Екатеринбург', 'region' => 'Свердловская область'],
            ['name' => 'Казань', 'region' => 'Республика Татарстан'],
            ['name' => 'Нижний Новгород', 'region' => 'Нижегородская область'],
            ['name' => 'Челябинск', 'region' => 'Челябинская область'],
            ['name' => 'Самара', 'region' => 'Самарская область'],
            ['name' => 'Омск', 'region' => 'Омская область'],
            ['name' => 'Ростов-на-Дону', 'region' => 'Ростовская область'],
            ['name' => 'Уфа', 'region' => 'Республика Башкортостан'],
            ['name' => 'Красноярск', 'region' => 'Красноярский край'],
            ['name' => 'Воронеж', 'region' => 'Воронежская область'],
            ['name' => 'Пермь', 'region' => 'Пермский край'],
            ['name' => 'Волгоград', 'region' => 'Волгоградская область'],
        ];

        foreach ($cities as $city) {
            City::query()->updateOrCreate(
                ['name' => $city['name']],
                [
                    'region' => $city['region'],
                    'is_active' => true,
                ],
            );
        }
    }
}
