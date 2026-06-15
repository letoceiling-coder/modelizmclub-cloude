<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Политика загрузки файлов «Моделизм»
|--------------------------------------------------------------------------
| Единый источник правды по доступным расширениям, MIME-типам, максимальному
| весу и параметрам обработки изображений. Используется в FormRequest'ах
| (валидация) и в сервисах обработки медиа (конверсии WebP, обрезка по ratio).
|
| Размеры заданы в килобайтах (как требует правило валидации Laravel `max:`).
*/

return [

    /*
     * Диск для хранения медиа. По умолчанию совпадает с media-library (public).
     * При переключении на S3 Selectel задайте MEDIA_DISK=s3.
     */
    'disk' => env('MEDIA_DISK', 'public'),

    /*
     * Глобальный «жёсткий» предел и запрещённые расширения для media-library.
     * Это «последний рубеж»: даже если FormRequest пропустит, библиотека отклонит.
     */
    'global' => [
        'max_file_size_kb' => (int) env('UPLOAD_MAX_FILE_KB', 204800), // 200 МБ (видео)
        // Опасные расширения (исполняемые/скриптовые), запрещены всегда.
        'disallowed_extensions' => [
            'php', 'php3', 'php4', 'php5', 'php7', 'php8', 'phtml', 'phar',
            'exe', 'bat', 'cmd', 'com', 'sh', 'bash', 'bin', 'msi', 'app',
            'js', 'mjs', 'jsp', 'asp', 'aspx', 'cgi', 'pl', 'py', 'rb',
            'htaccess', 'htm', 'html', 'svg', 'xml', 'svgz',
        ],
    ],

    /*
     * Параметры обработки изображений (общие для всех картинок).
     * format — целевой формат конверсий для ускоренной отдачи.
     */
    'images' => [
        'driver' => env('IMAGE_DRIVER', 'gd'),       // gd | imagick | vips
        'format' => env('IMAGE_FORMAT', 'webp'),      // webp для быстрой загрузки
        'quality' => (int) env('IMAGE_QUALITY', 82),
        'optimize' => (bool) env('IMAGE_OPTIMIZE', true),
        // Допустимые соотношения сторон (для информации/будущей валидации кропа).
        'allowed_ratios' => ['1:1', '4:5', '3:4', '16:9', '4:3'],
    ],

    /*
     * Профили загрузки по типам сущностей. Каждый профиль описывает,
     * что можно загружать и какие конверсии (размер + кроп по ratio) строить.
     *
     * conversions:
     *   width/height — целевой размер
     *   fit          — 'crop' (обрезка по ratio) | 'contain' (вписать без обрезки)
     */
    'profiles' => [

        'avatar' => [
            'extensions' => ['jpg', 'jpeg', 'png', 'webp'],
            'mimes' => ['image/jpeg', 'image/png', 'image/webp'],
            'max_size_kb' => (int) env('UPLOAD_AVATAR_MAX_KB', 5120), // 5 МБ
            'is_image' => true,
            'conversions' => [
                'thumb' => ['width' => 128, 'height' => 128, 'fit' => 'crop'],
                'medium' => ['width' => 512, 'height' => 512, 'fit' => 'crop'],
            ],
        ],

        'post_photo' => [
            'extensions' => ['jpg', 'jpeg', 'png', 'webp', 'gif'],
            'mimes' => ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
            'max_size_kb' => (int) env('UPLOAD_PHOTO_MAX_KB', 15360), // 15 МБ
            'max_count' => (int) env('MODELIZM_POST_MAX_PHOTOS', 10),
            'is_image' => true,
            'conversions' => [
                'thumb' => ['width' => 400, 'height' => 400, 'fit' => 'crop'],
                'medium' => ['width' => 1080, 'height' => null, 'fit' => 'contain'],
                'large' => ['width' => 1920, 'height' => null, 'fit' => 'contain'],
            ],
        ],

        'post_video' => [
            'extensions' => ['mp4', 'webm', 'mov'],
            'mimes' => ['video/mp4', 'video/webm', 'video/quicktime'],
            'max_size_kb' => (int) env('MODELIZM_POST_MAX_VIDEO_SIZE_MB', 200) * 1024,
            'is_image' => false,
        ],

        'ad_photo' => [
            'extensions' => ['jpg', 'jpeg', 'png', 'webp'],
            'mimes' => ['image/jpeg', 'image/png', 'image/webp'],
            'max_size_kb' => (int) env('UPLOAD_PHOTO_MAX_KB', 15360),
            'max_count' => (int) env('UPLOAD_AD_MAX_PHOTOS', 10),
            'is_image' => true,
            'conversions' => [
                'thumb' => ['width' => 400, 'height' => 400, 'fit' => 'crop'],
                'medium' => ['width' => 1080, 'height' => null, 'fit' => 'contain'],
            ],
        ],

        'community_avatar' => [
            'extensions' => ['jpg', 'jpeg', 'png', 'webp'],
            'mimes' => ['image/jpeg', 'image/png', 'image/webp'],
            'max_size_kb' => (int) env('UPLOAD_AVATAR_MAX_KB', 5120),
            'is_image' => true,
            'conversions' => [
                'thumb' => ['width' => 200, 'height' => 200, 'fit' => 'crop'],
            ],
        ],

        'community_cover' => [
            'extensions' => ['jpg', 'jpeg', 'png', 'webp'],
            'mimes' => ['image/jpeg', 'image/png', 'image/webp'],
            'max_size_kb' => (int) env('UPLOAD_PHOTO_MAX_KB', 15360),
            'is_image' => true,
            'conversions' => [
                'medium' => ['width' => 1280, 'height' => 400, 'fit' => 'crop'],
            ],
        ],

        'message_attachment' => [
            'extensions' => ['jpg', 'jpeg', 'png', 'webp', 'gif', 'pdf'],
            'mimes' => ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'application/pdf'],
            'max_size_kb' => (int) env('UPLOAD_ATTACHMENT_MAX_KB', 20480), // 20 МБ
            'is_image' => false,
            'conversions' => [
                'thumb' => ['width' => 400, 'height' => 400, 'fit' => 'contain'],
            ],
        ],
    ],
];
