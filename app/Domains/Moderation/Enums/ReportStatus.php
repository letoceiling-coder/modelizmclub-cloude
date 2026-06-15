<?php

declare(strict_types=1);

namespace App\Domains\Moderation\Enums;

use App\Support\Concerns\EnumHelpers;

enum ReportStatus: string
{
    use EnumHelpers;

    case Open = 'open';
    case Reviewing = 'reviewing';
    case Resolved = 'resolved';
    case Dismissed = 'dismissed';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Открыта',
            self::Reviewing => 'В работе',
            self::Resolved => 'Решена',
            self::Dismissed => 'Отклонена',
        };
    }
}
