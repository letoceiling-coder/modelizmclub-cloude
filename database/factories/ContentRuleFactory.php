<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domains\Moderation\Enums\ContentRuleAction;
use App\Domains\Moderation\Enums\ContentRuleType;
use App\Domains\Moderation\Models\ContentRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContentRule>
 */
class ContentRuleFactory extends Factory
{
    protected $model = ContentRule::class;

    public function definition(): array
    {
        return [
            'type' => ContentRuleType::StopWord->value,
            'value' => fake()->unique()->word(),
            'action' => ContentRuleAction::Block->value,
            'is_active' => true,
        ];
    }

    public function bannedLink(): static
    {
        return $this->state(fn () => [
            'type' => ContentRuleType::BannedLink->value,
            'action' => ContentRuleAction::Flag->value,
        ]);
    }
}
