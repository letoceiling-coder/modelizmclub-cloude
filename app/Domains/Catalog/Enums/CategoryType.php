<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Enums;

use App\Support\Concerns\EnumHelpers;

/**
 * Раздельные справочники категорий:
 *  - content   — для публикаций ленты и интересов пользователей
 *  - community — для сообществ
 *  - ad        — для объявлений
 */
enum CategoryType: string
{
    use EnumHelpers;

    case Content = 'content';
    case Community = 'community';
    case Ad = 'ad';

    public function label(): string
    {
        return match ($this) {
            self::Content => 'Категория публикаций',
            self::Community => 'Категория сообществ',
            self::Ad => 'Категория объявлений',
        };
    }
}
