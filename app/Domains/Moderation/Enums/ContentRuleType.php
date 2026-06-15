<?php

declare(strict_types=1);

namespace App\Domains\Moderation\Enums;

use App\Support\Concerns\EnumHelpers;

enum ContentRuleType: string
{
    use EnumHelpers;

    case StopWord = 'stopword';
    case BannedLink = 'banned_link';

    public function label(): string
    {
        return match ($this) {
            self::StopWord => 'Стоп-слово',
            self::BannedLink => 'Запрещённая ссылка',
        };
    }
}
