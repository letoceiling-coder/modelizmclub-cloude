<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domains\Catalog\Enums\CategoryType;
use App\Domains\Catalog\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'type' => CategoryType::Content->value,
            'name' => Str::ucfirst($name),
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(4)),
            'icon' => null,
            'position' => fake()->numberBetween(0, 100),
            'is_active' => true,
        ];
    }

    public function ofType(CategoryType $type): static
    {
        return $this->state(fn () => ['type' => $type->value]);
    }
}
