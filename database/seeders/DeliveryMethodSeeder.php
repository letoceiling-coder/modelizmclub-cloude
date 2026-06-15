<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domains\Catalog\Models\DeliveryMethod;
use Illuminate\Database\Seeder;

class DeliveryMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            ['name' => 'Самовывоз', 'slug' => 'pickup'],
            ['name' => 'Почта России', 'slug' => 'russian-post'],
            ['name' => 'СДЭК', 'slug' => 'cdek'],
            ['name' => 'Boxberry', 'slug' => 'boxberry'],
            ['name' => 'Курьер', 'slug' => 'courier'],
            ['name' => 'Авито Доставка', 'slug' => 'avito-delivery'],
        ];

        foreach ($methods as $position => $method) {
            DeliveryMethod::query()->updateOrCreate(
                ['slug' => $method['slug']],
                [
                    'name' => $method['name'],
                    'position' => $position,
                    'is_active' => true,
                ],
            );
        }
    }
}
