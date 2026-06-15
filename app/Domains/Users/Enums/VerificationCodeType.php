<?php

declare(strict_types=1);

namespace App\Domains\Users\Enums;

use App\Support\Concerns\EnumHelpers;

enum VerificationCodeType: string
{
    use EnumHelpers;

    case EmailVerification = 'email_verification';
    case PasswordReset = 'password_reset';

    public function label(): string
    {
        return match ($this) {
            self::EmailVerification => 'Подтверждение почты',
            self::PasswordReset => 'Сброс пароля',
        };
    }

    public function ttlMinutes(): int
    {
        return match ($this) {
            self::EmailVerification => 30,
            self::PasswordReset => 60,
        };
    }
}
