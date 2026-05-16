<?php

use App\Http\Controllers\Admin\Audit\ActivityLogController;
use App\Http\Controllers\Admin\Audit\AuditDashboardController;
use App\Http\Controllers\Admin\Audit\LoginHistoryController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('system')->name('system.')->group(function () {
    Route::get('/audit', [AuditDashboardController::class, 'index'])
        ->middleware('permission:audit.view')
        ->name('audit.index');

    Route::get('/activity-logs', [ActivityLogController::class, 'index'])
        ->middleware('permission:audit.logs.view')
        ->name('activity-logs.index');

    Route::get('/login-history', [LoginHistoryController::class, 'index'])
        ->middleware('permission:audit.logins.view')
        ->name('login-history.index');
});