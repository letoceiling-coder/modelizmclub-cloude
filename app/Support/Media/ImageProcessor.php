<?php

declare(strict_types=1);

namespace App\Support\Media;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Image\Enums\Fit;
use Spatie\Image\Image;

/**
 * Лёгкая синхронная обработка одиночных изображений (например, аватаров):
 * приведение к WebP, обрезка по соотношению сторон, сохранение на нужный диск.
 *
 * Для коллекций (посты, объявления) используется media-library с очередями.
 */
final class ImageProcessor
{
    /**
     * Конвертирует изображение в WebP с обрезкой crop W×H и кладёт на диск.
     * Возвращает относительный путь сохранённого файла.
     */
    public static function storeWebp(
        UploadedFile $file,
        string $directory,
        int $width,
        int $height,
        ?string $disk = null,
    ): string {
        $disk ??= config('uploads.disk', config('media-library.disk_name', 'public'));
        $quality = (int) config('uploads.images.quality', 82);

        $tmp = tempnam(sys_get_temp_dir(), 'mz_').'.webp';

        Image::useImageDriver(config('uploads.images.driver', 'gd'))
            ->loadFile($file->getRealPath())
            ->fit(Fit::Crop, $width, $height)
            ->quality($quality)
            ->save($tmp);

        $path = trim($directory, '/').'/'.Str::uuid()->toString().'.webp';
        Storage::disk($disk)->put($path, (string) file_get_contents($tmp), 'public');

        @unlink($tmp);

        return $path;
    }
}
