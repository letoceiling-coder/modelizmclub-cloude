<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Настройки платформы «Моделизм»
|--------------------------------------------------------------------------
| Базовые (дефолтные) значения. Динамически изменяемые администратором
| параметры хранятся в таблице settings (spatie/laravel-settings) и при
| наличии переопределяют значения отсюда.
*/

return [

    // Лента и публикации
    'posts' => [
        'max_photos' => (int) env('MODELIZM_POST_MAX_PHOTOS', 10),
        'max_video_seconds' => (int) env('MODELIZM_POST_MAX_VIDEO_SECONDS', 180),
        'max_video_size_mb' => (int) env('MODELIZM_POST_MAX_VIDEO_SIZE_MB', 200),
        'title_max' => 255,
        'body_max' => 20000,
        'comment_max_depth' => 10,
    ],

    // Объявления (этап 2)
    'ads' => [
        'free_per_subscriber' => (int) env('MODELIZM_ADS_FREE_PER_SUBSCRIBER', 5),
        'default_active_days' => (int) env('MODELIZM_ADS_ACTIVE_DAYS', 30),
        'guest_max_photos' => 5,
        'price_min' => 0,
        'price_max' => 1_000_000_000,
    ],

    // Медиа
    'media' => [
        'image_mimes' => ['jpg', 'jpeg', 'png', 'webp', 'gif'],
        'image_max_size_kb' => (int) env('MODELIZM_IMAGE_MAX_KB', 15360),
        'video_mimes' => ['mp4', 'webm', 'mov'],
        'avatar_max_size_kb' => 5120,
        'disk' => env('MODELIZM_MEDIA_DISK', env('FILESYSTEM_DISK', 'public')),
    ],

    // Бонусная система
    'bonuses' => [
        'accrual_percent' => (float) env('MODELIZM_BONUS_PERCENT', 5),
        'validity_days' => (int) env('MODELIZM_BONUS_VALIDITY_DAYS', 365),
        'max_spend_percent' => (float) env('MODELIZM_BONUS_MAX_SPEND_PERCENT', 50),
    ],

    // Пагинация
    'pagination' => [
        'default_per_page' => 20,
        'max_per_page' => 100,
        'feed_per_page' => 15,
    ],

    // Платёжные провайдеры
    'payments' => [
        'default_provider' => env('MODELIZM_PAYMENT_PROVIDER', 'vtb'),
        'currency' => 'RUB',
    ],

    // Лимиты запросов (rate limiting)
    'rate_limit' => [
        'api_per_minute' => (int) env('RATE_LIMIT_API', 120),
        'guest_per_minute' => (int) env('RATE_LIMIT_GUEST', 40),
        'uploads_per_minute' => (int) env('RATE_LIMIT_UPLOADS', 30),
    ],
];
