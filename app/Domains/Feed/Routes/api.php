<?php

declare(strict_types=1);

use App\Domains\Feed\Http\Controllers\BookmarkController;
use App\Domains\Feed\Http\Controllers\CommentController;
use App\Domains\Feed\Http\Controllers\PostController;
use App\Domains\Feed\Http\Controllers\ReactionController;
use Illuminate\Support\Facades\Route;

// Публичное чтение ленты
Route::get('posts', [PostController::class, 'index']);
Route::get('posts/{post}', [PostController::class, 'show']);
Route::get('posts/{post}/comments', [CommentController::class, 'index']);
Route::get('comments/{comment}/replies', [CommentController::class, 'replies']);

Route::middleware('auth:sanctum')->group(function (): void {
    // Посты (создание/обновление — со строгим лимитом из-за загрузки медиа)
    Route::post('posts', [PostController::class, 'store'])->middleware('throttle:uploads');
    Route::match(['put', 'patch'], 'posts/{post}', [PostController::class, 'update'])->middleware('throttle:uploads');
    Route::delete('posts/{post}', [PostController::class, 'destroy']);
    Route::post('posts/{post}/pin', [PostController::class, 'pin']);
    Route::delete('posts/{post}/pin', [PostController::class, 'unpin']);

    // Реакции
    Route::post('posts/{post}/reactions', [ReactionController::class, 'togglePost']);
    Route::post('comments/{comment}/reactions', [ReactionController::class, 'toggleComment']);

    // Закладки
    Route::get('bookmarks', [BookmarkController::class, 'index']);
    Route::post('posts/{post}/bookmark', [BookmarkController::class, 'toggle']);

    // Комментарии
    Route::post('posts/{post}/comments', [CommentController::class, 'store']);
    Route::delete('comments/{comment}', [CommentController::class, 'destroy']);
});
