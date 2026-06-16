<?php

use App\Http\Controllers\MediaStreamController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// API-only: подсказки для QA/сканеров, которые обращаются к web /login и /register.
Route::get('/login', static fn () => response()->json([
    'message' => 'Это API-only backend. Для входа используйте POST /api/v1/auth/login',
    'endpoint' => url('/api/v1/auth/login'),
    'docs' => url('/docs/api'),
], 200))->name('login.hint');

Route::get('/register', static fn () => response()->json([
    'message' => 'Это API-only backend. Для регистрации используйте POST /api/v1/auth/register',
    'endpoint' => url('/api/v1/auth/register'),
    'docs' => url('/docs/api'),
    'required_fields' => ['name', 'email', 'password', 'password_confirmation', 'consent'],
], 200))->name('register.hint');

// Отдача медиа с S3 через поддомен img.* (приватный бакет Selectel).
$mediaHost = config('media.host');
$appHost = parse_url((string) config('app.url', ''), PHP_URL_HOST);

if (is_string($mediaHost) && $mediaHost !== '' && $mediaHost !== $appHost) {
    Route::domain($mediaHost)
        ->get('{path}', MediaStreamController::class)
        ->where('path', '.+')
        ->name('media.stream');
}
