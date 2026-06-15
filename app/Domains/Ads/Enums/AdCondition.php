<?php

declare(strict_types=1);

namespace App\Domains\Ads\Enums;

use App\Support\Concerns\EnumHelpers;

enum AdCondition: string
{
    use EnumHelpers;

    case New = 'new';
    case Used = 'used';

    public function label(): string
    {
        return match ($this) {
            self::New => 'Новое',
            self::Used => 'Б/у',
        };
    }
}
