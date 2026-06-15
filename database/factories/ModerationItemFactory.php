<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domains\Feed\Models\Post;
use App\Domains\Moderation\Enums\ModerationStatus;
use App\Domains\Moderation\Models\ModerationItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ModerationItem>
 */
class ModerationItemFactory extends Factory
{
    protected $model = ModerationItem::class;

    public function definition(): array
    {
        return [
            'moderatable_id' => Post::factory()->pending(),
            'moderatable_type' => 'post',
            'status' => ModerationStatus::Pending->value,
            'submitted_at' => now(),
            'flags' => [],
        ];
    }
}
