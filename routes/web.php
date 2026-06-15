<?php

use App\Http\Controllers\MediaStreamController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Отдача медиа с S3 через поддомен img.* (приватный бакет Selectel).
$mediaHost = config('media.host');
$appHost = parse_url((string) config('app.url', ''), PHP_URL_HOST);

if (is_string($mediaHost) && $mediaHost !== '' && $mediaHost !== $appHost) {
    Route::domain($mediaHost)
        ->get('{path}', MediaStreamController::class)
        ->where('path', '.+')
        ->name('media.stream');
}
