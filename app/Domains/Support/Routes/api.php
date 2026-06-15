<?php

declare(strict_types=1);

use App\Domains\Support\Http\Controllers\KbArticleController;
use App\Domains\Support\Http\Controllers\LegalDocumentController;
use App\Domains\Support\Http\Controllers\SupportTicketController;
use Illuminate\Support\Facades\Route;

// База знаний и юридические документы — публично
Route::get('kb/articles', [KbArticleController::class, 'index']);
Route::get('kb/articles/{kbArticle}', [KbArticleController::class, 'show']);
Route::get('legal', [LegalDocumentController::class, 'index']);
Route::get('legal/{type}', [LegalDocumentController::class, 'show']);

// Тикеты поддержки
Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('support/tickets', [SupportTicketController::class, 'index']);
    Route::post('support/tickets', [SupportTicketController::class, 'store']);
    Route::get('support/tickets/{ticket}', [SupportTicketController::class, 'show']);
    Route::post('support/tickets/{ticket}/reply', [SupportTicketController::class, 'reply']);
});
