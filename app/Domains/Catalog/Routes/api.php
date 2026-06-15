<?php

declare(strict_types=1);

use App\Domains\Catalog\Http\Controllers\CategoryController;
use App\Domains\Catalog\Http\Controllers\CityController;
use App\Domains\Catalog\Http\Controllers\DeliveryMethodController;
use Illuminate\Support\Facades\Route;

// Публичные справочники
Route::get('categories', [CategoryController::class, 'index']);
Route::get('categories/{category}', [CategoryController::class, 'show']);
Route::get('cities', [CityController::class, 'index']);
Route::get('delivery-methods', [DeliveryMethodController::class, 'index']);

// Управление деревом категорий (требуются права categories.manage)
Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('categories', [CategoryController::class, 'store']);
    Route::post('categories/reorder', [CategoryController::class, 'reorder']);
    Route::match(['put', 'patch'], 'categories/{category}', [CategoryController::class, 'update']);
    Route::delete('categories/{category}', [CategoryController::class, 'destroy']);
});
