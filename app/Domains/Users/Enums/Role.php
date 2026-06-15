<?php

declare(strict_types=1);

namespace App\Domains\Users\Enums;

use App\Support\Concerns\EnumHelpers;

/**
 * Роли пользователей платформы (spatie/laravel-permission).
 *
 * Роль "Подписчик" назначается автоматически при активной подписке,
 * но также является полноценной ролью для матрицы прав.
 */
enum Role: string
{
    use EnumHelpers;

    case Admin = 'admin';
    case Moderator = 'moderator';
    case Subscriber = 'subscriber';
    case User = 'user';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Администратор',
            self::Moderator => 'Модератор',
            self::Subscriber => 'Подписчик',
            self::User => 'Пользователь',
        };
    }
}
