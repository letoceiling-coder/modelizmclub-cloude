<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domains\Communities\Models\Community;
use App\Domains\Communities\Models\CommunitySection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CommunitySection>
 */
class CommunitySectionFactory extends Factory
{
    protected $model = CommunitySection::class;

    public function definition(): array
    {
        return [
            'community_id' => Community::factory(),
            'name' => fake()->words(2, true),
            'position' => fake()->numberBetween(0, 20),
            'is_active' => true,
        ];
    }
}
