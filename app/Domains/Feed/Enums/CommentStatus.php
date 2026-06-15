<?php

declare(strict_types=1);

namespace App\Domains\Feed\Enums;

use App\Support\Concerns\EnumHelpers;

enum CommentStatus: string
{
    use EnumHelpers;

    case Published = 'published';
    case Pending = 'pending';
    case Hidden = 'hidden';

    public function label(): string
    {
        return match ($this) {
            self::Published => 'Опубликован',
            self::Pending => 'На модерации',
            self::Hidden => 'Скрыт',
        };
    }
}
