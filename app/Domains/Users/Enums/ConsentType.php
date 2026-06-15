<?php

declare(strict_types=1);

namespace App\Domains\Users\Enums;

use App\Support\Concerns\EnumHelpers;

enum ConsentType: string
{
    use EnumHelpers;

    case PersonalDataProcessing = 'pd_processing';
    case Terms = 'terms';

    public function label(): string
    {
        return match ($this) {
            self::PersonalDataProcessing => 'Согласие на обработку персональных данных',
            self::Terms => 'Пользовательское соглашение',
        };
    }
}
