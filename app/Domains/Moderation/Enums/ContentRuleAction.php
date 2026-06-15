<?php

declare(strict_types=1);

namespace App\Domains\Moderation\Enums;

use App\Support\Concerns\EnumHelpers;

enum ContentRuleAction: string
{
    use EnumHelpers;

    case Block = 'block';
    case Flag = 'flag';

    public function label(): string
    {
        return match ($this) {
            self::Block => 'Блокировать',
            self::Flag => 'Помечать для модерации',
        };
    }
}
