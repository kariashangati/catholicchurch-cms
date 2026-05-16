<?php

use App\Http\Controllers\PaymentController;
use App\Services\MpesaService;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('payments.create');
});

Route::get('/pay', [PaymentController::class, 'create'])->name('payments.create');
Route::post('/pay', [PaymentController::class, 'store'])->name('payments.store');
Route::get('/payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');

/*
|--------------------------------------------------------------------------
| Test SessionKey
|--------------------------------------------------------------------------
| Use this first before testing checkout.
*/
Route::get('/test-mpesa-session', function (MpesaService $mpesa) {
    return response()->json([
        'session_key' => $mpesa->getSessionKey(),
    ]);
});