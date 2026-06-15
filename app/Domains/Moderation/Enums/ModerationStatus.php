<?php

declare(strict_types=1);

namespace App\Domains\Moderation\Enums;

use App\Support\Concerns\EnumHelpers;

enum ModerationStatus: string
{
    use EnumHelpers;

    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case NeedsRevision = 'needs_revision';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'На модерации',
            self::Approved => 'Одобрено',
            self::Rejected => 'Отклонено',
            self::NeedsRevision => 'На доработку',
        };
    }
}
