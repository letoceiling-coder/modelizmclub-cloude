<?php

declare(strict_types=1);

namespace App\Domains\Ads\Enums;

use App\Support\Concerns\EnumHelpers;

enum AiDraftStatus: string
{
    use EnumHelpers;

    case Pending = 'pending';
    case Processing = 'processing';
    case Completed = 'completed';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'В очереди',
            self::Processing => 'Обрабатывается',
            self::Completed => 'Готово',
            self::Failed => 'Ошибка',
        };
    }
}
