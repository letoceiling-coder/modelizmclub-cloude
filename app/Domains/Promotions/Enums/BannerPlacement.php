<?php

declare(strict_types=1);

namespace App\Domains\Promotions\Enums;

use App\Support\Concerns\EnumHelpers;

enum BannerPlacement: string
{
    use EnumHelpers;

    case FeedTop = 'feed_top';
    case FeedInline = 'feed_inline';
    case Sidebar = 'sidebar';

    public function label(): string
    {
        return match ($this) {
            self::FeedTop => 'Верх ленты',
            self::FeedInline => 'Между постами',
            self::Sidebar => 'Боковая панель',
        };
    }
}
