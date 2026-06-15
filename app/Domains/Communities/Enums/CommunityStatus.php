<?php

declare(strict_types=1);

namespace App\Domains\Communities\Enums;

use App\Support\Concerns\EnumHelpers;

enum CommunityStatus: string
{
    use EnumHelpers;

    case Pending = 'pending';
    case Active = 'active';
    case Blocked = 'blocked';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'На модерации',
            self::Active => 'Активно',
            self::Blocked => 'Заблокировано',
        };
    }
}
