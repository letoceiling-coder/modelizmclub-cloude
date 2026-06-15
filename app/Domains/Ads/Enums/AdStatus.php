<?php

declare(strict_types=1);

namespace App\Domains\Ads\Enums;

use App\Support\Concerns\EnumHelpers;

enum AdStatus: string
{
    use EnumHelpers;

    case Draft = 'draft';
    case AwaitingPayment = 'awaiting_payment';
    case Pending = 'pending';
    case Published = 'published';
    case Rejected = 'rejected';
    case Removed = 'removed';
    case Sold = 'sold';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Черновик',
            self::AwaitingPayment => 'Ожидает оплаты',
            self::Pending => 'На модерации',
            self::Published => 'Опубликовано',
            self::Rejected => 'Отклонено',
            self::Removed => 'Снято',
            self::Sold => 'Продано',
            self::Expired => 'Истёк срок',
        };
    }
}
