<?php

use App\Http\Controllers\Admin\HallBooking\HallBlockedDateController;
use App\Http\Controllers\Admin\HallBooking\HallBookingController;
use App\Http\Controllers\Admin\HallBooking\HallBookingDashboardController;
use App\Http\Controllers\Admin\HallBooking\HallController;
use App\Http\Controllers\Admin\HallBooking\HallPriceRuleController;
use App\Http\Controllers\Frontend\HallBookingPageController;
use App\Http\Middleware\SetLocale;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Hall Booking / Ukumbi Routes
|--------------------------------------------------------------------------
| Register this file in routes/web.php using:
| require __DIR__.'/booking.php';
*/
Route::middleware([SetLocale::class])->group(function (): void {
    Route::get('/book-hall', [HallBookingPageController::class, 'index'])
        ->name('frontend.hall-bookings.index');

    Route::get('/book-hall/track', [HallBookingPageController::class, 'track'])
        ->name('frontend.hall-bookings.track');

    Route::post('/book-hall/track', [HallBookingPageController::class, 'submitTrack'])
        ->name('frontend.hall-bookings.track.submit');

    Route::post('/book-hall/track/payment', [HallBookingPageController::class, 'submitPayment'])
        ->name('frontend.hall-bookings.payment.submit');

    Route::get('/book-hall/confirmation/{reference}', [HallBookingPageController::class, 'confirmation'])
        ->name('frontend.hall-bookings.confirmation');

    Route::get('/book-hall/{hall:slug}', [HallBookingPageController::class, 'show'])
        ->name('frontend.hall-bookings.show');

    Route::get('/book-hall/{hall:slug}/availability', [HallBookingPageController::class, 'availability'])
        ->name('frontend.hall-bookings.availability');

    Route::post('/book-hall/{hall:slug}/book', [HallBookingPageController::class, 'store'])
        ->name('frontend.hall-bookings.store');
});

/*
|--------------------------------------------------------------------------
| Admin Hall Booking Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])
    ->prefix('hall-bookings')
    ->name('hall-bookings.')
    ->group(function (): void {
        Route::get('/dashboard', [HallBookingDashboardController::class, 'index'])
            ->middleware('permission:hall-bookings.dashboard.view')
            ->name('dashboard');

        Route::get('/halls', [HallController::class, 'index'])
            ->middleware('permission:hall-bookings.halls.view')
            ->name('halls.index');
        Route::post('/halls', [HallController::class, 'store'])
            ->middleware('permission:hall-bookings.halls.create')
            ->name('halls.store');
        Route::put('/halls/{hall}', [HallController::class, 'update'])
            ->middleware('permission:hall-bookings.halls.update')
            ->name('halls.update');
        Route::delete('/halls/{hall}', [HallController::class, 'destroy'])
            ->middleware('permission:hall-bookings.halls.delete')
            ->name('halls.destroy');
        Route::delete('/halls/{hall}/images/{image}', [HallController::class, 'deleteImage'])
            ->middleware('permission:hall-bookings.halls.update')
            ->name('halls.images.destroy');

        Route::get('/prices', [HallPriceRuleController::class, 'index'])
            ->middleware('permission:hall-bookings.prices.manage')
            ->name('prices.index');
        Route::post('/prices', [HallPriceRuleController::class, 'store'])
            ->middleware('permission:hall-bookings.prices.manage')
            ->name('prices.store');
        Route::put('/prices/{priceRule}', [HallPriceRuleController::class, 'update'])
            ->middleware('permission:hall-bookings.prices.manage')
            ->name('prices.update');
        Route::delete('/prices/{priceRule}', [HallPriceRuleController::class, 'destroy'])
            ->middleware('permission:hall-bookings.prices.manage')
            ->name('prices.destroy');

        Route::get('/blocked-dates', [HallBlockedDateController::class, 'index'])
            ->middleware('permission:hall-bookings.blocked-dates.manage')
            ->name('blocked-dates.index');
        Route::post('/blocked-dates', [HallBlockedDateController::class, 'store'])
            ->middleware('permission:hall-bookings.blocked-dates.manage')
            ->name('blocked-dates.store');
        Route::delete('/blocked-dates/{blockedDate}', [HallBlockedDateController::class, 'destroy'])
            ->middleware('permission:hall-bookings.blocked-dates.manage')
            ->name('blocked-dates.destroy');

        Route::get('/bookings', [HallBookingController::class, 'index'])
            ->middleware('permission:hall-bookings.bookings.view')
            ->name('bookings.index');
        Route::post('/bookings', [HallBookingController::class, 'store'])
            ->middleware('permission:hall-bookings.bookings.update')
            ->name('bookings.store');
        Route::get('/bookings/{booking}', [HallBookingController::class, 'show'])
            ->middleware('permission:hall-bookings.bookings.view')
            ->name('bookings.show');
        Route::put('/bookings/{booking}', [HallBookingController::class, 'update'])
            ->middleware('permission:hall-bookings.bookings.update')
            ->name('bookings.update');
        Route::post('/bookings/{booking}/change-status', [HallBookingController::class, 'changeStatus'])
            ->middleware('permission:hall-bookings.bookings.update')
            ->name('bookings.change-status');
        Route::post('/bookings/{booking}/verify-payment', [HallBookingController::class, 'verifyPayment'])
            ->middleware('permission:hall-bookings.bookings.verify-payment')
            ->name('bookings.verify-payment');
        Route::post('/bookings/{booking}/approve', [HallBookingController::class, 'approve'])
            ->middleware('permission:hall-bookings.bookings.approve')
            ->name('bookings.approve');
        Route::post('/bookings/{booking}/reject', [HallBookingController::class, 'reject'])
            ->middleware('permission:hall-bookings.bookings.reject')
            ->name('bookings.reject');
        Route::delete('/bookings/{booking}', [HallBookingController::class, 'destroy'])
            ->middleware('permission:hall-bookings.bookings.cancel')
            ->name('bookings.destroy');
    });
