<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domains\Promotions\Models\BonusAccount;
use App\Domains\Users\Enums\UserStatus;
use App\Domains\Users\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@modelizmclub.ru'],
            [
                'name' => 'Администратор',
                'username' => 'admin',
                'password' => Hash::make('password'),
                'status' => UserStatus::Active->value,
                'email_verified_at' => now(),
            ],
        );

        if (! $admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        BonusAccount::query()->firstOrCreate(['user_id' => $admin->id], ['balance' => 0]);
    }
}
