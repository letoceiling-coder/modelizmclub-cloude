<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domains\Catalog\Models\City;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<City>
 */
class CityFactory extends Factory
{
    protected $model = City::class;

    public function definition(): array
    {
        return [
            'name' => \fake()->city(),
            'region' => \fake()->word().' область',
            'is_active' => true,
        ];
    }
}
