<?php

declare(strict_types=1);

namespace App\Support\Media;

/**
 * Единая точка построения правил валидации загрузок из config/uploads.php.
 * Гарантирует, что контроллеры/реквесты и обработка медиа используют один
 * и тот же список расширений, MIME-типов и лимитов размера.
 */
final class UploadPolicy
{
    /**
     * @return array<string, mixed>
     */
    public static function profile(string $name): array
    {
        return (array) config("uploads.profiles.{$name}", []);
    }

    /**
     * Правила валидации для одного загружаемого файла указанного профиля.
     *
     * @return array<int, string>
     */
    public static function fileRules(string $name): array
    {
        $p = self::profile($name);

        $rules = ['file'];

        if ($p['is_image'] ?? false) {
            $rules[] = 'image';
        }

        if (! empty($p['mimes'])) {
            $rules[] = 'mimetypes:'.implode(',', (array) $p['mimes']);
        }

        if (! empty($p['extensions'])) {
            $rules[] = 'extensions:'.implode(',', (array) $p['extensions']);
        }

        if (! empty($p['max_size_kb'])) {
            $rules[] = 'max:'.(int) $p['max_size_kb'];
        }

        return $rules;
    }

    /**
     * Правила для массива файлов (например, photos): [array-правила, item-правила].
     *
     * @return array{0: array<int, string>, 1: array<int, string>}
     */
    public static function arrayRules(string $name): array
    {
        $arrayRules = ['nullable', 'array'];

        $max = self::maxCount($name);
        if ($max > 0) {
            $arrayRules[] = "max:{$max}";
        }

        return [$arrayRules, self::fileRules($name)];
    }

    public static function maxCount(string $name): int
    {
        return (int) (self::profile($name)['max_count'] ?? 1);
    }

    public static function maxSizeKb(string $name): int
    {
        return (int) (self::profile($name)['max_size_kb'] ?? 0);
    }
}
