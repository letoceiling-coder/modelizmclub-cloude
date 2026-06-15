<?php

declare(strict_types=1);

use App\Domains\Communities\Http\Controllers\CommunityApplicationController;
use App\Domains\Communities\Http\Controllers\CommunityController;
use App\Domains\Communities\Http\Controllers\CommunityMemberController;
use App\Domains\Communities\Http\Controllers\CommunitySectionController;
use Illuminate\Support\Facades\Route;

// Публичное чтение
Route::get('communities', [CommunityController::class, 'index']);
Route::get('communities/{community}', [CommunityController::class, 'show']);
Route::get('communities/{community}/members', [CommunityMemberController::class, 'index']);
Route::get('communities/{community}/sections', [CommunitySectionController::class, 'index']);

Route::middleware('auth:sanctum')->group(function (): void {
    // Заявки на создание сообщества
    Route::get('community-applications', [CommunityApplicationController::class, 'index']);
    Route::post('community-applications', [CommunityApplicationController::class, 'store']);
    Route::post('community-applications/{application}/approve', [CommunityApplicationController::class, 'approve']);
    Route::post('community-applications/{application}/reject', [CommunityApplicationController::class, 'reject']);

    // Управление сообществом
    Route::match(['put', 'patch'], 'communities/{community}', [CommunityController::class, 'update']);
    Route::delete('communities/{community}', [CommunityController::class, 'destroy']);

    // Участие
    Route::post('communities/{community}/join', [CommunityMemberController::class, 'join']);
    Route::delete('communities/{community}/leave', [CommunityMemberController::class, 'leave']);
    Route::patch('communities/{community}/members/{user}', [CommunityMemberController::class, 'updateRole']);
    Route::delete('communities/{community}/members/{user}', [CommunityMemberController::class, 'remove']);

    // Разделы
    Route::post('communities/{community}/sections', [CommunitySectionController::class, 'store']);
    Route::match(['put', 'patch'], 'communities/{community}/sections/{section}', [CommunitySectionController::class, 'update']);
    Route::delete('communities/{community}/sections/{section}', [CommunitySectionController::class, 'destroy']);
});
