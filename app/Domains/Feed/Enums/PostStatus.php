<?php

declare(strict_types=1);

namespace App\Domains\Feed\Enums;

use App\Support\Concerns\EnumHelpers;

enum PostStatus: string
{
    use EnumHelpers;

    case Draft = 'draft';
    case Pending = 'pending';
    case Published = 'published';
    case Rejected = 'rejected';
    case Hidden = 'hidden';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Черновик',
            self::Pending => 'На модерации',
            self::Published => 'Опубликовано',
            self::Rejected => 'Отклонено',
            self::Hidden => 'Скрыто',
        };
    }

    public function isVisible(): bool
    {
        return $this === self::Published;
    }
}
