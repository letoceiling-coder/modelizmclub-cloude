<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domains\Promotions\Models\BonusAccount;
use App\Domains\Users\Enums\ConsentType;
use App\Domains\Users\Enums\UserStatus;
use App\Domains\Users\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Демо-пользователь для QA / Swagger Try It / E2E.
 * Учётные данные задаются через SANCTUM_EMAIL и SANCTUM_PASSWORD в .env.
 */
class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = (string) config('sanctum.email');
        $password = (string) config('sanctum.password');

        $user = User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Demo User',
                'username' => 'demo_qa',
                'password' => Hash::make($password),
                'status' => UserStatus::Active->value,
                'email_verified_at' => now(),
            ],
        );

        if (! $user->hasRole('user')) {
            $user->assignRole('user');
        }

        BonusAccount::query()->firstOrCreate(['user_id' => $user->id], ['balance' => 0]);

        if (! $user->consents()->where('type', ConsentType::PersonalDataProcessing->value)->exists()) {
            $user->consents()->create([
                'type' => ConsentType::PersonalDataProcessing->value,
                'version' => '1.0',
                'accepted_at' => now(),
                'ip' => '127.0.0.1',
                'user_agent' => 'seeder',
            ]);
        }
    }
}
