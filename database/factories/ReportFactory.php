<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domains\Feed\Models\Post;
use App\Domains\Moderation\Enums\ReportStatus;
use App\Domains\Moderation\Models\Report;
use App\Domains\Users\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Report>
 */
class ReportFactory extends Factory
{
    protected $model = Report::class;

    public function definition(): array
    {
        return [
            'reporter_id' => User::factory(),
            'reportable_id' => Post::factory(),
            'reportable_type' => 'post',
            'reason' => 'спам',
            'description' => fake()->sentence(),
            'status' => ReportStatus::Open->value,
        ];
    }
}
