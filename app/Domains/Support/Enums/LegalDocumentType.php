<?php

declare(strict_types=1);

namespace App\Domains\Support\Enums;

use App\Support\Concerns\EnumHelpers;

enum LegalDocumentType: string
{
    use EnumHelpers;

    case Terms = 'terms';
    case Privacy = 'privacy';
    case Refund = 'refund';
    case PdConsent = 'pd_consent';

    public function label(): string
    {
        return match ($this) {
            self::Terms => 'Пользовательское соглашение',
            self::Privacy => 'Политика конфиденциальности',
            self::Refund => 'Условия возврата',
            self::PdConsent => 'Согласие на обработку ПД',
        };
    }
}
