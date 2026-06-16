<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domains\Feed\Enums\PostStatus;
use App\Domains\Feed\Models\Post;
use App\Domains\Users\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => \fake()->optional()->sentence(),
            'body' => \fake()->paragraph(),
            'status' => PostStatus::Published->value,
            'published_at' => now(),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => [
            'status' => PostStatus::Draft->value,
            'published_at' => null,
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn () => [
            'status' => PostStatus::Pending->value,
            'published_at' => null,
        ]);
    }
}
