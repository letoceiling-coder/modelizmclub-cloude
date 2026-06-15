<?php

declare(strict_types=1);

use App\Domains\Moderation\Http\Controllers\ContentRuleController;
use App\Domains\Moderation\Http\Controllers\ModerationController;
use App\Domains\Moderation\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function (): void {
    // Любой авторизованный пользователь может пожаловаться
    Route::post('reports', [ReportController::class, 'store']);

    // Модерация и работа с жалобами — модераторы и администраторы
    Route::middleware('role:admin|moderator')->group(function (): void {
        Route::get('moderation/queue', [ModerationController::class, 'index']);
        Route::get('moderation/queue/{item}', [ModerationController::class, 'show']);
        Route::post('moderation/queue/{item}/approve', [ModerationController::class, 'approve']);
        Route::post('moderation/queue/{item}/reject', [ModerationController::class, 'reject']);
        Route::post('moderation/queue/{item}/needs-revision', [ModerationController::class, 'needsRevision']);

        Route::get('reports', [ReportController::class, 'index']);
        Route::post('reports/{report}/resolve', [ReportController::class, 'resolve']);
        Route::post('reports/{report}/dismiss', [ReportController::class, 'dismiss']);
    });

    // Правила контента (стоп-слова/ссылки) — администраторы
    Route::apiResource('content-rules', ContentRuleController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->parameters(['content-rules' => 'contentRule']);
});
