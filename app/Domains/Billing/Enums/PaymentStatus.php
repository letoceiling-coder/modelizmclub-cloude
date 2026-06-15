<?php

declare(strict_types=1);

namespace App\Domains\Billing\Enums;

use App\Support\Concerns\EnumHelpers;

enum PaymentStatus: string
{
    use EnumHelpers;

    case Pending = 'pending';
    case Paid = 'paid';
    case Failed = 'failed';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';
    case PaidByBonus = 'paid_by_bonus';
    case PromoApplied = 'promo_applied';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Ожидает оплаты',
            self::Paid => 'Оплачено',
            self::Failed => 'Ошибка',
            self::Cancelled => 'Отменено',
            self::Refunded => 'Возвращено',
            self::PaidByBonus => 'Оплачено бонусами',
            self::PromoApplied => 'Применён промокод',
        };
    }

    public function isSuccessful(): bool
    {
        return in_array($this, [self::Paid, self::PaidByBonus, self::PromoApplied], true);
    }
}
