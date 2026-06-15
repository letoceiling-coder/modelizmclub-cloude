<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API v1
|--------------------------------------------------------------------------
| Базовый префикс задан в bootstrap/app.php (apiPrefix: 'api/v1').
| Маршруты разнесены по доменам.
*/

Route::get('/ping', fn () => ['status' => 'ok', 'service' => 'modelizm-api', 'version' => 'v1']);

foreach ([
    'Users',
    'Catalog',
    'Feed',
    'Communities',
    'Moderation',
    'Admin',
    'Messaging',
    'Ads',
    'Billing',
    'Promotions',
    'Support',
    'Notifications',
] as $domain) {
    $path = app_path("Domains/{$domain}/Routes/api.php");

    if (file_exists($path)) {
        require $path;
    }
}
