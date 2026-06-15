<?php

declare(strict_types=1);

use App\Domains\Feed\Models\Post;
use App\Domains\Users\Enums\UserStatus;
use App\Domains\Users\Models\User;
use Database\Seeders\RolePermissionSeeder;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;
use function Pest\Laravel\seed;

beforeEach(function () {
    seed(RolePermissionSeeder::class);
});

function admin(): User
{
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    return $admin;
}

it('blocks the admin dashboard for non-admins', function () {
    actingAs(User::factory()->create())
        ->getJson('/api/v1/admin/dashboard')
        ->assertForbidden();
});

it('returns dashboard statistics for an admin', function () {
    Post::factory()->count(3)->create();

    actingAs(admin())
        ->getJson('/api/v1/admin/dashboard')
        ->assertOk()
        ->assertJsonPath('data.content.posts', 3)
        ->assertJsonStructure(['data' => ['users', 'content', 'moderation']]);
});

it('lists and searches users', function () {
    User::factory()->create(['name' => 'Иван Петров']);

    actingAs(admin())
        ->getJson('/api/v1/admin/users?filter[search]=Петров')
        ->assertOk()
        ->assertJsonPath('data.0.name', 'Иван Петров');
});

it('bans and unbans a user and revokes tokens', function () {
    $target = User::factory()->create();
    $target->createToken('app');

    actingAs(admin())
        ->postJson("/api/v1/admin/users/{$target->id}/ban", ['reason' => 'Нарушение правил'])
        ->assertOk()
        ->assertJsonPath('data.status', UserStatus::Banned->value);

    expect($target->fresh()->tokens()->count())->toBe(0);

    actingAs(admin())
        ->postJson("/api/v1/admin/users/{$target->id}/unban")
        ->assertOk()
        ->assertJsonPath('data.status', UserStatus::Active->value);
});

it('syncs user roles', function () {
    $target = User::factory()->create();

    actingAs(admin())
        ->putJson("/api/v1/admin/users/{$target->id}/roles", ['roles' => ['moderator']])
        ->assertOk()
        ->assertJsonPath('data.roles.0', 'moderator');

    expect($target->fresh()->hasRole('moderator'))->toBeTrue();
});
