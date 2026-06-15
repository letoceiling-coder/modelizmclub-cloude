<?php

declare(strict_types=1);

use App\Domains\Communities\Enums\CommunityMemberRole;
use App\Domains\Communities\Models\Community;
use App\Domains\Communities\Models\CommunityApplication;
use App\Domains\Users\Models\User;
use Database\Seeders\RolePermissionSeeder;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;
use function Pest\Laravel\seed;

it('lists active communities only', function () {
    Community::factory()->count(2)->create();
    Community::factory()->pending()->create();

    getJson('/api/v1/communities')
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

it('lets a user submit a community application', function () {
    $user = User::factory()->create();
    $category = \App\Domains\Catalog\Models\Category::factory()->create();

    actingAs($user)
        ->postJson('/api/v1/community-applications', [
            'proposed_name' => 'Клуб авиамоделистов',
            'category_id' => $category->id,
            'description' => 'Сообщество любителей сборки авиамоделей.',
        ])
        ->assertCreated()
        ->assertJsonPath('data.status', 'pending');
});

it('lets a moderator approve an application and creates the community', function () {
    seed(RolePermissionSeeder::class);
    $moderator = User::factory()->create();
    $moderator->assignRole('moderator');

    $application = CommunityApplication::factory()->create();

    actingAs($moderator)
        ->postJson("/api/v1/community-applications/{$application->id}/approve")
        ->assertCreated()
        ->assertJsonPath('data.status', 'active');

    $community = Community::query()->where('name', $application->proposed_name)->first();
    expect($community)->not->toBeNull();
    expect($community->members()->where('user_id', $application->user_id)->where('role', CommunityMemberRole::Moderator->value)->exists())->toBeTrue();
});

it('lets a user join and leave a community', function () {
    $user = User::factory()->create();
    $community = Community::factory()->create();

    actingAs($user)
        ->postJson("/api/v1/communities/{$community->slug}/join")
        ->assertCreated();

    expect($community->members()->where('user_id', $user->id)->count())->toBe(1);

    actingAs($user)
        ->deleteJson("/api/v1/communities/{$community->slug}/leave")
        ->assertOk();

    expect($community->members()->where('user_id', $user->id)->count())->toBe(0);
});

it('forbids a stranger from creating a community section', function () {
    $stranger = User::factory()->create();
    $community = Community::factory()->create();

    actingAs($stranger)
        ->postJson("/api/v1/communities/{$community->slug}/sections", ['name' => 'Новости'])
        ->assertForbidden();
});

it('lets the owner create a community section', function () {
    $owner = User::factory()->create();
    $community = Community::factory()->create(['owner_id' => $owner->id]);

    actingAs($owner)
        ->postJson("/api/v1/communities/{$community->slug}/sections", ['name' => 'Новости'])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Новости');
});
