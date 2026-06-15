<?php

declare(strict_types=1);

use App\Domains\Admin\Http\Controllers\AuditLogController;
use App\Domains\Admin\Http\Controllers\DashboardController;
use App\Domains\Admin\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'role:admin'])
    ->prefix('admin')
    ->group(function (): void {
        Route::get('dashboard', DashboardController::class);
        Route::get('audit-log', [AuditLogController::class, 'index']);

        Route::get('users', [UserManagementController::class, 'index']);
        Route::get('users/{user}', [UserManagementController::class, 'show']);
        Route::post('users/{user}/ban', [UserManagementController::class, 'ban']);
        Route::post('users/{user}/unban', [UserManagementController::class, 'unban']);
        Route::put('users/{user}/roles', [UserManagementController::class, 'syncRoles']);
    });
