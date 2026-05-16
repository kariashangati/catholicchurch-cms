<?php

use App\Http\Controllers\Admin\ContactMessages\ContactMessageController;
use App\Http\Controllers\Admin\ContactMessages\ContactMessageDashboardController;
use App\Http\Controllers\Admin\ContactMessages\ContactReasonController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])
    ->prefix('contact-messages')
    ->name('contact-messages.')
    ->group(function (): void {
        Route::get('/dashboard', [ContactMessageDashboardController::class, 'index'])
            ->middleware('permission:contact-messages.dashboard.view')
            ->name('dashboard');

        Route::get('/', [ContactMessageController::class, 'index'])
            ->middleware('permission:contact-messages.view')
            ->name('index');
        Route::post('/{contactMessage}/read', [ContactMessageController::class, 'markRead'])
            ->middleware('permission:contact-messages.update')
            ->name('mark-read');
        Route::post('/{contactMessage}/assign', [ContactMessageController::class, 'assign'])
            ->middleware('permission:contact-messages.assign')
            ->name('assign');
        Route::post('/{contactMessage}/answer', [ContactMessageController::class, 'answer'])
            ->middleware('permission:contact-messages.answer')
            ->name('answer');
        Route::post('/{contactMessage}/status', [ContactMessageController::class, 'changeStatus'])
            ->middleware('permission:contact-messages.update')
            ->name('change-status');
        Route::delete('/{contactMessage}', [ContactMessageController::class, 'destroy'])
            ->middleware('permission:contact-messages.delete')
            ->name('destroy');

        Route::get('/reasons/list', [ContactReasonController::class, 'index'])
            ->middleware('permission:contact-messages.reasons.manage')
            ->name('reasons.index');
        Route::post('/reasons', [ContactReasonController::class, 'store'])
            ->middleware('permission:contact-messages.reasons.manage')
            ->name('reasons.store');
        Route::put('/reasons/{reason}', [ContactReasonController::class, 'update'])
            ->middleware('permission:contact-messages.reasons.manage')
            ->name('reasons.update');
        Route::post('/reasons/{reason}/toggle', [ContactReasonController::class, 'toggle'])
            ->middleware('permission:contact-messages.reasons.manage')
            ->name('reasons.toggle');
        Route::delete('/reasons/{reason}', [ContactReasonController::class, 'destroy'])
            ->middleware('permission:contact-messages.reasons.manage')
            ->name('reasons.destroy');
    });
