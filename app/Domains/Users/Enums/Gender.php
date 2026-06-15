<?php

declare(strict_types=1);

namespace App\Domains\Users\Enums;

use App\Support\Concerns\EnumHelpers;

enum Gender: string
{
    use EnumHelpers;

    case Male = 'male';
    case Female = 'female';
    case Unspecified = 'unspecified';

    public function label(): string
    {
        return match ($this) {
            self::Male => 'Мужской',
            self::Female => 'Женский',
            self::Unspecified => 'Не указан',
        };
    }
}
