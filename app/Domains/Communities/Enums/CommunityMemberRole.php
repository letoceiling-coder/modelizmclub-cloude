<?php

declare(strict_types=1);

namespace App\Domains\Communities\Enums;

use App\Support\Concerns\EnumHelpers;

enum CommunityMemberRole: string
{
    use EnumHelpers;

    case Member = 'member';
    case Moderator = 'moderator';

    public function label(): string
    {
        return match ($this) {
            self::Member => 'Участник',
            self::Moderator => 'Модератор сообщества',
        };
    }
}
