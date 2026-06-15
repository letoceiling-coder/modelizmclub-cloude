<?php

declare(strict_types=1);

namespace App\Support\Concerns;

/**
 * Вспомогательные методы для строковых enum-ов статусов.
 *
 * Требует, чтобы enum реализовывал метод label(): string.
 */
trait EnumHelpers
{
    /**
     * Список всех значений enum-а.
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }

    /**
     * Список значений для правила валидации Rule::in().
     *
     * @return array<int, string>
     */
    public static function valuesFor(self ...$cases): array
    {
        return array_map(static fn (self $case): string => $case->value, $cases);
    }

    /**
     * Опции "значение => русская подпись" для админки и фронта.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}
