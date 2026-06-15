<?php

declare(strict_types=1);

namespace App\Domains\Billing\Enums;

use App\Support\Concerns\EnumHelpers;

enum SubscriptionStatus: string
{
    use EnumHelpers;

    case Pending = 'pending';
    case Active = 'active';
    case Expired = 'expired';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Ожидает оплаты',
            self::Active => 'Активна',
            self::Expired => 'Истекла',
            self::Cancelled => 'Отменена',
        };
    }
}
