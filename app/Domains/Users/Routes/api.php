<?php

declare(strict_types=1);

use App\Domains\Users\Http\Controllers\AuthController;
use App\Domains\Users\Http\Controllers\EmailVerificationController;
use App\Domains\Users\Http\Controllers\PasswordResetController;
use App\Domains\Users\Http\Controllers\ProfileController;
use App\Domains\Users\Http\Controllers\SocialAuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function (): void {
    Route::post('register', [AuthController::class, 'register'])->middleware('throttle:6,1');
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::post('email/verify', [EmailVerificationController::class, 'verify'])->middleware('throttle:10,1');
    Route::post('email/resend', [EmailVerificationController::class, 'resend'])->middleware('throttle:3,1');
    Route::post('password/forgot', [PasswordResetController::class, 'forgot'])->middleware('throttle:3,1');
    Route::post('password/reset', [PasswordResetController::class, 'reset'])->middleware('throttle:6,1');

    Route::get('social/{provider}/redirect', [SocialAuthController::class, 'redirect']);
    Route::get('social/{provider}/callback', [SocialAuthController::class, 'callback']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
    });
});

Route::get('users/{user}', [ProfileController::class, 'show']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::patch('profile', [ProfileController::class, 'update']);
    Route::put('profile/privacy', [ProfileController::class, 'updatePrivacy']);
    Route::put('profile/interests', [ProfileController::class, 'syncInterests']);
    Route::post('profile/avatar', [ProfileController::class, 'uploadAvatar'])->middleware('throttle:uploads');
    Route::post('users/{user}/block', [ProfileController::class, 'block']);
    Route::delete('users/{user}/block', [ProfileController::class, 'unblock']);
});
