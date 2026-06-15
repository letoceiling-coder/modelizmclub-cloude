<?php

declare(strict_types=1);

namespace App\Domains\Communities\Enums;

use App\Support\Concerns\EnumHelpers;

enum CommunityApplicationStatus: string
{
    use EnumHelpers;

    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'На рассмотрении',
            self::Approved => 'Одобрена',
            self::Rejected => 'Отклонена',
        };
    }
}
