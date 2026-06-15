<?php

declare(strict_types=1);

namespace App\Domains\Billing\Enums;

use App\Support\Concerns\EnumHelpers;

enum PlanPeriod: string
{
    use EnumHelpers;

    case Month = 'month';
    case Year = 'year';
    case Lifetime = 'lifetime';

    public function label(): string
    {
        return match ($this) {
            self::Month => 'Месяц',
            self::Year => 'Год',
            self::Lifetime => 'Бессрочно',
        };
    }

    public function days(): ?int
    {
        return match ($this) {
            self::Month => 30,
            self::Year => 365,
            self::Lifetime => null,
        };
    }
}
