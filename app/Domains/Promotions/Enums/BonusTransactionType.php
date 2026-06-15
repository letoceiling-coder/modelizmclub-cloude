<?php

declare(strict_types=1);

namespace App\Domains\Promotions\Enums;

use App\Support\Concerns\EnumHelpers;

enum BonusTransactionType: string
{
    use EnumHelpers;

    case Earn = 'earn';
    case Spend = 'spend';
    case Expire = 'expire';
    case Adjust = 'adjust';

    public function label(): string
    {
        return match ($this) {
            self::Earn => 'Начисление',
            self::Spend => 'Списание',
            self::Expire => 'Сгорание',
            self::Adjust => 'Корректировка',
        };
    }
}
