<?php

use App\Http\Controllers\MafundishoEnrollmentController;
use App\Http\Controllers\TeachingTypeController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function (): void {
    Route::prefix('mafundisho')
        ->name('mafundisho.')
        ->group(function (): void {
            Route::get('/', [MafundishoEnrollmentController::class, 'summary'])
                ->middleware('can:mafundisho.view')
                ->name('summary');

            Route::get('/types', [TeachingTypeController::class, 'index'])
                ->middleware('can:mafundisho-types.view')
                ->name('types.index');

            Route::post('/types', [TeachingTypeController::class, 'store'])
                ->middleware('can:mafundisho-types.create')
                ->name('types.store');

            Route::put('/types/{teaching_type}', [TeachingTypeController::class, 'update'])
                ->middleware('can:mafundisho-types.update')
                ->name('types.update');

            Route::delete('/types/{teaching_type}', [TeachingTypeController::class, 'destroy'])
                ->middleware('can:mafundisho-types.delete')
                ->name('types.destroy');

            Route::get('/{type}/{year?}', [MafundishoEnrollmentController::class, 'index'])
                ->middleware('can:mafundisho.view')
                ->whereNumber('year')
                ->name('index');

            Route::post('/enrollments', [MafundishoEnrollmentController::class, 'store'])
                ->middleware('can:mafundisho.create')
                ->name('enrollments.store');

            Route::put('/enrollments/{mafundisho_enrollment}', [MafundishoEnrollmentController::class, 'update'])
                ->middleware('can:mafundisho.update')
                ->name('enrollments.update');

            Route::delete('/enrollments/{mafundisho_enrollment}', [MafundishoEnrollmentController::class, 'destroy'])
                ->middleware('can:mafundisho.delete')
                ->name('enrollments.destroy');
        });
});