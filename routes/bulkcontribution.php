<?php

use App\Http\Controllers\Admin\Finance\ContributionBulkController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])
    ->prefix('finance/contributions/bulk')
    ->name('finance.contributions.bulk.')
    ->group(function () {
        Route::get('/', [ContributionBulkController::class, 'index'])
            ->middleware('permission:finance.contributions.bulk.view')
            ->name('index');

        Route::post('/', [ContributionBulkController::class, 'store'])
            ->middleware('permission:finance.contributions.bulk.create')
            ->name('store');
    });
