<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domains\Communities\Enums\CommunityApplicationStatus;
use App\Domains\Communities\Models\CommunityApplication;
use App\Domains\Users\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CommunityApplication>
 */
class CommunityApplicationFactory extends Factory
{
    protected $model = CommunityApplication::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'category_id' => null,
            'proposed_name' => fake()->unique()->company(),
            'description' => fake()->paragraph(),
            'status' => CommunityApplicationStatus::Pending->value,
        ];
    }
}
