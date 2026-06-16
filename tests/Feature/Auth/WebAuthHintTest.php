<?php

declare(strict_types=1);

use Database\Seeders\DemoUserSeeder;
use Database\Seeders\RolePermissionSeeder;

use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
});

it('returns API hint on GET /login instead of 500', function () {
    $response = getJson('/login')->assertOk();
    expect($response->json('endpoint'))->toContain('/api/v1/auth/login');
});

it('returns API hint on GET /register instead of 500', function () {
    $response = getJson('/register')->assertOk();
    expect($response->json('endpoint'))->toContain('/api/v1/auth/register');
});

it('logs in with seeded demo credentials from config', function () {
    $this->seed(DemoUserSeeder::class);

    postJson('/api/v1/auth/login', [
        'email' => config('sanctum.email'),
        'password' => config('sanctum.password'),
    ])->assertOk()->assertJsonStructure(['data', 'token']);
});
