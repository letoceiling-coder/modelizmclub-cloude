<?php

declare(strict_types=1);

namespace App\Domains\Users\Enums;

use App\Support\Concerns\EnumHelpers;

enum UserStatus: string
{
    use EnumHelpers;

    case Active = 'active';
    case Banned = 'banned';
    case Deleted = 'deleted';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Активен',
            self::Banned => 'Заблокирован',
            self::Deleted => 'Удалён',
        };
    }
}
