<?php

use App\Http\Controllers\TranslationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:Super Admin'])->group(function (): void {
    Route::get('/translations', [TranslationController::class, 'index'])->name('translations.index');
    Route::get('/translations/create', [TranslationController::class, 'create'])->name('translations.create');
    Route::post('/translations', [TranslationController::class, 'store'])->name('translations.store');
    Route::post('/translations/bulk-replace', [TranslationController::class, 'bulkReplace'])->name('translations.bulk-replace');
    Route::get('/translations/{translation}/edit', [TranslationController::class, 'edit'])->name('translations.edit');
    Route::put('/translations/{translation}', [TranslationController::class, 'update'])->name('translations.update');
    Route::delete('/translations/{translation}', [TranslationController::class, 'destroy'])->name('translations.destroy');
});
