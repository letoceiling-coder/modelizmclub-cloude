<?php

declare(strict_types=1);

namespace App\Domains\Users\Services;

use App\Domains\Promotions\Models\BonusAccount;
use App\Domains\Users\Enums\ConsentType;
use App\Domains\Users\Enums\Role;
use App\Domains\Users\Enums\UserStatus;
use App\Domains\Users\Enums\VerificationCodeType;
use App\Domains\Users\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role as SpatieRole;

class AuthService
{
    public function __construct(
        private readonly VerificationCodeService $codes,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function register(array $data, Request $request): User
    {
        return DB::transaction(function () use ($data, $request): User {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => Hash::make($data['password']),
                'status' => UserStatus::Active->value,
            ]);

            SpatieRole::findOrCreate(Role::User->value, 'web');
            $user->assignRole(Role::User->value);

            BonusAccount::firstOrCreate(['user_id' => $user->id], ['balance' => 0]);

            // Согласие 152-ФЗ фиксируем с метаданными
            $user->consents()->create([
                'type' => ConsentType::PersonalDataProcessing->value,
                'version' => '1.0',
                'accepted_at' => now(),
                'ip' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 255),
            ]);

            $this->codes->generateAndSend($user->email, VerificationCodeType::EmailVerification, $user);

            return $user;
        });
    }

    /**
     * @return array{user: User, token: string}
     *
     * @throws ValidationException
     */
    public function login(string $email, string $password, string $deviceName = 'api'): array
    {
        $user = User::where('email', $email)->first();

        if ($user === null || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'Неверная почта или пароль.',
            ]);
        }

        if ($user->status === UserStatus::Banned) {
            throw ValidationException::withMessages([
                'email' => 'Учётная запись заблокирована.',
            ]);
        }

        $user->forceFill(['last_seen_at' => now()])->save();

        $token = $user->createToken($deviceName)->plainTextToken;

        return ['user' => $user, 'token' => $token];
    }

    public function verifyEmail(string $email, string $code): User
    {
        $this->codes->verify($email, VerificationCodeType::EmailVerification, $code);

        $user = User::where('email', $email)->firstOrFail();

        if ($user->email_verified_at === null) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        return $user;
    }
}
