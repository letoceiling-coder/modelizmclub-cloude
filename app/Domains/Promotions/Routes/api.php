<?php

declare(strict_types=1);

use App\Domains\Promotions\Http\Controllers\BannerController;
use App\Domains\Promotions\Http\Controllers\PromoCodeController;
use Illuminate\Support\Facades\Route;

Route::get('banners', [BannerController::class, 'index']);
Route::post('banners/{banner}/click', [BannerController::class, 'click']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('promo-codes/validate', [PromoCodeController::class, 'validateCode']);
});
