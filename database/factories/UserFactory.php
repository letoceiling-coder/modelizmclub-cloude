<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domains\Users\Enums\Gender;
use App\Domains\Users\Enums\UserStatus;
use App\Domains\Users\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => \fake()->name(),
            'username' => \fake()->unique()->userName(),
            'email' => \fake()->unique()->safeEmail(),
            'phone' => \fake()->optional()->numerify('+7##########'),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'bio' => \fake()->optional()->sentence(),
            'gender' => \fake()->randomElement(Gender::values()),
            'status' => UserStatus::Active->value,
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function banned(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => UserStatus::Banned->value,
        ]);
    }
}
