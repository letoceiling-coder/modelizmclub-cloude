<?php

declare(strict_types=1);

use App\Domains\Users\Enums\VerificationCodeType;
use App\Domains\Users\Models\User;
use App\Domains\Users\Models\VerificationCode;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
});

it('регистрирует пользователя и выдаёт токен', function (): void {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Иван Петров',
        'email' => 'ivan@example.com',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
        'consent' => true,
    ]);

    $response->assertCreated()
        ->assertJsonStructure(['data' => ['id', 'name'], 'token']);

    $user = User::where('email', 'ivan@example.com')->first();
    expect($user)->not->toBeNull()
        ->and($user->hasRole('user'))->toBeTrue()
        ->and($user->consents()->count())->toBe(1)
        ->and($user->bonusAccount)->not->toBeNull();

    expect(VerificationCode::where('target', 'ivan@example.com')
        ->where('type', VerificationCodeType::EmailVerification->value)->exists())->toBeTrue();
});

it('требует согласие на обработку ПД', function (): void {
    $this->postJson('/api/v1/auth/register', [
        'name' => 'Без согласия',
        'email' => 'noconsent@example.com',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
    ])->assertStatus(422)->assertJsonValidationErrorFor('consent');
});

it('логинит пользователя и возвращает токен', function (): void {
    $user = User::factory()->create(['email' => 'log@example.com']);

    $this->postJson('/api/v1/auth/login', [
        'email' => 'log@example.com',
        'password' => 'password',
    ])->assertOk()->assertJsonStructure(['data' => ['id'], 'token']);
});

it('отклоняет неверный пароль', function (): void {
    User::factory()->create(['email' => 'log2@example.com']);

    $this->postJson('/api/v1/auth/login', [
        'email' => 'log2@example.com',
        'password' => 'wrong-password',
    ])->assertStatus(422);
});

it('защищает /auth/me от анонимов', function (): void {
    $this->getJson('/api/v1/auth/me')->assertUnauthorized();
});

it('возвращает текущего пользователя по токену', function (): void {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $this->withToken($token)
        ->getJson('/api/v1/auth/me')
        ->assertOk()
        ->assertJsonPath('data.id', $user->id);
});

it('подтверждает почту корректным кодом', function (): void {
    $user = User::factory()->unverified()->create(['email' => 'verify@example.com']);
    VerificationCode::create([
        'user_id' => $user->id,
        'target' => 'verify@example.com',
        'code' => '123456',
        'type' => VerificationCodeType::EmailVerification->value,
        'expires_at' => now()->addMinutes(30),
    ]);

    $this->postJson('/api/v1/auth/email/verify', [
        'email' => 'verify@example.com',
        'code' => '123456',
    ])->assertOk();

    expect($user->fresh()->email_verified_at)->not->toBeNull();
});
