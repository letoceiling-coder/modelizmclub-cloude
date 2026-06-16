<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domains\Communities\Enums\CommunityStatus;
use App\Domains\Communities\Models\Community;
use App\Domains\Users\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Community>
 */
class CommunityFactory extends Factory
{
    protected $model = Community::class;

    public function definition(): array
    {
        $name = \fake()->unique()->company();

        return [
            'owner_id' => User::factory(),
            'category_id' => null,
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(5)),
            'description' => \fake()->optional()->paragraph(),
            'status' => CommunityStatus::Active->value,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => ['status' => CommunityStatus::Pending->value]);
    }
}
