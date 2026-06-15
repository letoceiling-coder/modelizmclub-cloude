<?php

declare(strict_types=1);

namespace App\Domains\Promotions\Enums;

use App\Support\Concerns\EnumHelpers;

enum PromoCodeType: string
{
    use EnumHelpers;

    case SubscriptionDiscount = 'subscription_discount';
    case FreePeriod = 'free_period';
    case FreeAds = 'free_ads';
    case Discount = 'discount';

    public function label(): string
    {
        return match ($this) {
            self::SubscriptionDiscount => 'Скидка на подписку',
            self::FreePeriod => 'Бесплатный период подписки',
            self::FreeAds => 'Бесплатные объявления',
            self::Discount => 'Скидка',
        };
    }
}
