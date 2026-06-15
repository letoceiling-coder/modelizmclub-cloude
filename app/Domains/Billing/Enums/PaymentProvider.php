<?php

declare(strict_types=1);

namespace App\Domains\Billing\Enums;

use App\Support\Concerns\EnumHelpers;

enum PaymentProvider: string
{
    use EnumHelpers;

    case Vtb = 'vtb';
    case YooKassa = 'yookassa';
    case Bonus = 'bonus';

    public function label(): string
    {
        return match ($this) {
            self::Vtb => 'Эквайринг ВТБ',
            self::YooKassa => 'ЮKassa',
            self::Bonus => 'Бонусные баллы',
        };
    }
}
