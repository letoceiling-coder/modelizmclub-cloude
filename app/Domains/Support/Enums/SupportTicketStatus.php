<?php

declare(strict_types=1);

namespace App\Domains\Support\Enums;

use App\Support\Concerns\EnumHelpers;

enum SupportTicketStatus: string
{
    use EnumHelpers;

    case Open = 'open';
    case InProgress = 'in_progress';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Открыто',
            self::InProgress => 'В работе',
            self::Closed => 'Закрыто',
        };
    }
}
