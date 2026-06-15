<?php

declare(strict_types=1);

namespace App\Support\Media;

use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\Conversions\Conversion;

/**
 * Регистрирует конверсии изображений из config/uploads.php: размер, обрезку по
 * соотношению сторон (ratio) и формат WebP для ускоренной отдачи.
 *
 * Требует, чтобы модель использовала Spatie\MediaLibrary\InteractsWithMedia.
 */
trait HasConfiguredConversions
{
    /**
     * @return Conversion the conversion being added (provided by InteractsWithMedia)
     */
    abstract public function addMediaConversion(string $name): Conversion;

    /**
     * Зарегистрировать конверсии профиля (config/uploads.php) для коллекции.
     */
    protected function registerConfiguredConversions(string $profile, string $collection): void
    {
        $images = (array) config('uploads.images', []);
        $toWebp = ($images['format'] ?? 'webp') === 'webp';
        $quality = (int) ($images['quality'] ?? 82);

        $conversions = (array) config("uploads.profiles.{$profile}.conversions", []);

        foreach ($conversions as $name => $cfg) {
            $conversion = $this->addMediaConversion($name)
                ->performOnCollections($collection);

            $width = isset($cfg['width']) ? (int) $cfg['width'] : null;
            $height = isset($cfg['height']) ? (int) $cfg['height'] : null;
            $fit = $cfg['fit'] ?? 'contain';

            if ($fit === 'crop' && $width && $height) {
                $conversion->fit(Fit::Crop, $width, $height);
            } else {
                if ($width) {
                    $conversion->width($width);
                }
                if ($height) {
                    $conversion->height($height);
                }
            }

            if ($toWebp) {
                $conversion->format('webp');
            }

            $conversion->quality($quality);
        }
    }
}
