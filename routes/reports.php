<?php

use App\Http\Controllers\Admin\Reports\FinanceReportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])
    ->prefix('finance/reports')
    ->name('finance.reports.')
    ->group(function (): void {
        Route::get('/', [FinanceReportController::class, 'index'])
            ->middleware('permission:finance.reports.summary.view')
            ->name('index');

        Route::get('/financial-summary', [FinanceReportController::class, 'financialSummary'])
            ->middleware('permission:finance.reports.summary.view')
            ->name('financial-summary');

        Route::get('/income-breakdown', [FinanceReportController::class, 'incomeBreakdown'])
            ->middleware('permission:finance.reports.summary.view')
            ->name('income-breakdown');

        Route::get('/expense-breakdown', [FinanceReportController::class, 'expenseBreakdown'])
            ->middleware('permission:finance.reports.summary.view')
            ->name('expense-breakdown');

        Route::get('/contribution-compliance', [FinanceReportController::class, 'compliance'])
            ->middleware('permission:finance.reports.compliance.view')
            ->name('compliance');

        Route::get('/contribution-compliance/csv', [FinanceReportController::class, 'complianceCsv'])
            ->middleware('permission:finance.reports.compliance.export')
            ->name('compliance.csv');
    });
