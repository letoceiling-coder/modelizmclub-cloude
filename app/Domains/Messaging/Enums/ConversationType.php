<?php

declare(strict_types=1);

namespace App\Domains\Messaging\Enums;

use App\Support\Concerns\EnumHelpers;

enum ConversationType: string
{
    use EnumHelpers;

    case Private = 'private';
    case Group = 'group';
    case Community = 'community';

    public function label(): string
    {
        return match ($this) {
            self::Private => 'Личный',
            self::Group => 'Групповой',
            self::Community => 'Чат сообщества',
        };
    }
}
