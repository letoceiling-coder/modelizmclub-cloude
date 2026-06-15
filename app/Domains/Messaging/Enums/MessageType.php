<?php

declare(strict_types=1);

namespace App\Domains\Messaging\Enums;

use App\Support\Concerns\EnumHelpers;

enum MessageType: string
{
    use EnumHelpers;

    case Text = 'text';
    case Image = 'image';
    case File = 'file';
    case System = 'system';

    public function label(): string
    {
        return match ($this) {
            self::Text => 'Текст',
            self::Image => 'Изображение',
            self::File => 'Файл',
            self::System => 'Системное',
        };
    }
}
