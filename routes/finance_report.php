<?php

use App\Http\Controllers\Admin\Finance\GiversNonGiversReportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])
    ->prefix('finance/reports')
    ->name('finance.reports.')
    ->group(function () {
        Route::get('/waliotoa-wasiotoa', [GiversNonGiversReportController::class, 'index'])
            ->middleware('permission:finance.reports.waliotoa.view')
            ->name('waliotoa.index');
    });
