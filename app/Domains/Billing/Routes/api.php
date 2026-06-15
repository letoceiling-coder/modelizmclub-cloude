<?php

declare(strict_types=1);

use App\Domains\Billing\Http\Controllers\PlanController;
use App\Domains\Billing\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;

// Публичный прайс-лист тарифов
Route::get('plans', [PlanController::class, 'index']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('subscriptions', [SubscriptionController::class, 'index']);
    Route::post('subscriptions', [SubscriptionController::class, 'store']);
});
