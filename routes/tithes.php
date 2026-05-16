<?php

use App\Http\Controllers\Admin\Finance\TitheController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::prefix('finance/tithes')
        ->name('finance.tithes.')
        ->group(function () {
            Route::get('/activity-log', [TitheController::class, 'activityLog'])
                ->middleware('permission:finance.view|finance.tithes.view')
                ->name('activity-log');

            Route::get('/activity-log/date/{date}', [TitheController::class, 'activityDate'])
                ->middleware('permission:finance.view|finance.tithes.view')
                ->where('date', '[0-9]{4}-[0-9]{2}-[0-9]{2}')
                ->name('activity-date');

            Route::get('/activity-log/date/{date}/recorder/{recorder}', [TitheController::class, 'activityRecorder'])
                ->middleware('permission:finance.view|finance.tithes.view')
                ->where('date', '[0-9]{4}-[0-9]{2}-[0-9]{2}')
                ->name('activity-recorder');
        });
});