<?php

declare(strict_types=1);

use App\Domains\Catalog\Enums\CategoryType;
use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Models\City;
use App\Domains\Users\Models\User;
use Database\Seeders\RolePermissionSeeder;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\seed;

it('returns the content category tree', function () {
    $root = Category::factory()->create(['type' => CategoryType::Content->value]);
    Category::factory()->create(['type' => CategoryType::Content->value])->appendToNode($root)->save();
    Category::factory()->create(['type' => CategoryType::Community->value]);

    $response = getJson('/api/v1/categories?type=content');

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonCount(1, 'data.0.children');
});

it('rejects an unknown catalog type', function () {
    getJson('/api/v1/categories?type=bogus')->assertStatus(422);
});

it('forbids category creation without permission', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->postJson('/api/v1/categories', [
            'type' => CategoryType::Content->value,
            'name' => 'Новая категория',
        ])
        ->assertForbidden();
});

it('allows an admin to create a category', function () {
    seed(RolePermissionSeeder::class);
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    actingAs($admin)
        ->postJson('/api/v1/categories', [
            'type' => CategoryType::Content->value,
            'name' => 'Авиация',
        ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Авиация');
});

it('searches cities by name', function () {
    City::factory()->create(['name' => 'Москва']);
    City::factory()->create(['name' => 'Казань']);

    getJson('/api/v1/cities?q=Мос')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Москва');
});
