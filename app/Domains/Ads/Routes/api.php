<?php

declare(strict_types=1);

use App\Domains\Ads\Http\Controllers\AdAiDraftController;
use App\Domains\Ads\Http\Controllers\AdController;
use Illuminate\Support\Facades\Route;

// Публичная витрина объявлений
Route::get('ads', [AdController::class, 'index']);
Route::get('ads/{ad}', [AdController::class, 'show']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('ads', [AdController::class, 'store']);

    // ИИ-помощник для черновика объявления
    Route::post('ads/ai-drafts', [AdAiDraftController::class, 'store']);
    Route::get('ads/ai-drafts/{draft}', [AdAiDraftController::class, 'show']);
});
