<?php

declare(strict_types=1);

use App\Domains\Feed\Enums\PostStatus;
use App\Domains\Feed\Models\Post;
use App\Domains\Moderation\Models\ContentRule;
use App\Domains\Moderation\Models\ModerationItem;
use App\Domains\Moderation\Models\Report;
use App\Domains\Moderation\Services\ContentModerationService;
use App\Domains\Users\Models\User;
use Database\Seeders\RolePermissionSeeder;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;
use function Pest\Laravel\seed;

beforeEach(function () {
    seed(RolePermissionSeeder::class);
});

function moderator(): User
{
    $moderator = User::factory()->create();
    $moderator->assignRole('moderator');

    return $moderator;
}

it('routes a post with a stop-word into the moderation queue as pending', function () {
    ContentRule::factory()->create(['value' => 'запрещёнка']);
    ContentModerationService::flushCache();

    $user = User::factory()->create();

    actingAs($user)->postJson('/api/v1/posts', [
        'body' => 'Продаю запрещёнка по скидке',
    ])->assertCreated();

    $post = Post::query()->where('user_id', $user->id)->first();
    expect($post->status)->toBe(PostStatus::Pending);
    expect(ModerationItem::query()->where('moderatable_type', 'post')->count())->toBe(1);
});

it('lets a moderator approve a queued post and publishes it', function () {
    $item = ModerationItem::factory()->create();

    actingAs(moderator())
        ->postJson("/api/v1/moderation/queue/{$item->id}/approve")
        ->assertOk()
        ->assertJsonPath('data.status', 'approved');

    expect(Post::query()->find($item->moderatable_id)->status)->toBe(PostStatus::Published);
});

it('forbids the moderation queue for regular users', function () {
    actingAs(User::factory()->create())
        ->getJson('/api/v1/moderation/queue')
        ->assertForbidden();
});

it('lets any user file a report and a moderator resolve it', function () {
    $post = Post::factory()->create();

    actingAs(User::factory()->create())
        ->postJson('/api/v1/reports', [
            'target_type' => 'post',
            'target_id' => $post->id,
            'reason' => 'спам',
        ])
        ->assertCreated();

    $report = Report::query()->latest('id')->first();

    actingAs(moderator())
        ->postJson("/api/v1/reports/{$report->id}/resolve", ['resolution' => 'Удалено'])
        ->assertOk()
        ->assertJsonPath('data.status', 'resolved');
});

it('validates the report target exists', function () {
    actingAs(User::factory()->create())
        ->postJson('/api/v1/reports', [
            'target_type' => 'post',
            'target_id' => 999999,
            'reason' => 'спам',
        ])
        ->assertStatus(422);
});

it('lets an admin manage content rules and flushes the cache', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    actingAs($admin)
        ->postJson('/api/v1/content-rules', [
            'type' => 'stopword',
            'value' => 'новоеслово',
            'action' => 'block',
        ])
        ->assertCreated()
        ->assertJsonPath('data.value', 'новоеслово');
});
