<?php

use App\Http\Controllers\ReceiptAccessController;
use App\Http\Controllers\ReceiptVerificationController;
use App\Http\Controllers\Admin\Finance\ReceiptController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])
    ->prefix('admin/finance/receipts')
    ->name('receipts.')
    ->group(function (): void {
        Route::get('/', [ReceiptController::class, 'index'])->name('index');
        Route::get('/dashboard', [ReceiptController::class, 'index'])->name('dashboard');
        Route::get('/analytics', [ReceiptController::class, 'index'])->name('analytics');
        Route::get('/create', [ReceiptController::class, 'create'])->name('create');
        Route::get('/print', [ReceiptController::class, 'create'])->name('print');
        Route::get('/print-review', [ReceiptController::class, 'review'])->name('review.safe');
        Route::get('/review', [ReceiptController::class, 'review'])->name('review');
        Route::post('/issue', [ReceiptController::class, 'store'])->name('issue');
        Route::get('/history', [ReceiptController::class, 'history'])->name('history');
        Route::get('/pending', [ReceiptController::class, 'pending'])->name('pending');
        Route::get('/exceptions/list', [ReceiptController::class, 'exceptions'])->name('exceptions');
        Route::get('/{receipt}', [ReceiptController::class, 'show'])->name('show');
        Route::get('/{receipt}/pdf', [ReceiptController::class, 'downloadPdf'])->name('pdf.download');
        Route::post('/{receipt}/send-sms', [ReceiptController::class, 'sendSms'])->name('send-sms');
        Route::post('/{receipt}/resend-sms', [ReceiptController::class, 'resendSms'])->name('resend-sms');
        Route::post('/{receipt}/reprint', [ReceiptController::class, 'reprint'])->name('reprint');
    });

Route::get('/receipts/access/{token}', [ReceiptAccessController::class, 'access'])->name('receipt.access');
Route::get('/receipts/download/{token}', [ReceiptAccessController::class, 'download'])->name('receipt.download');

Route::get('/receipts/verify/search', [ReceiptVerificationController::class, 'search'])->name('receipt.verify.search');
Route::get('/receipts/verify', [ReceiptVerificationController::class, 'search'])->name('receipt.verify.form');
Route::get('/receipts/verify/{token}', [ReceiptVerificationController::class, 'show'])
    ->where('token', '^(?!search$).+')
    ->name('receipt.verify');
