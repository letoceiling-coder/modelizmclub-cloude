<?php

declare(strict_types=1);

namespace App\Domains\Feed\Enums;

use App\Support\Concerns\EnumHelpers;

enum ReactionType: string
{
    use EnumHelpers;

    case Like = 'like';
    case Love = 'love';
    case Fire = 'fire';
    case Laugh = 'laugh';
    case Wow = 'wow';

    public function label(): string
    {
        return match ($this) {
            self::Like => 'Нравится',
            self::Love => 'Восторг',
            self::Fire => 'Огонь',
            self::Laugh => 'Смех',
            self::Wow => 'Удивление',
        };
    }
}
