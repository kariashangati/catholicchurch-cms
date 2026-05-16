<?php

use App\Http\Controllers\Admin\Sermons\SermonController;
use App\Http\Controllers\Admin\Sermons\SermonDashboardController;
use App\Http\Controllers\Admin\Sermons\SermonRecipientController;
use App\Http\Controllers\Admin\Sermons\SermonRequestController;
use App\Http\Controllers\PublicSermons\PublicSermonController;
use App\Http\Controllers\Frontend\SermonRequestPublicController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])
    ->prefix('sermons')
    ->name('sermons.')
    ->group(function (): void {
        Route::get('/dashboard', [SermonDashboardController::class, 'index'])
            ->middleware('permission:sermons.dashboard.view')
            ->name('dashboard');

        Route::get('/', [SermonController::class, 'index'])
            ->middleware('permission:sermons.view')
            ->name('index');
        Route::get('/create', [SermonController::class, 'create'])
            ->middleware('permission:sermons.create')
            ->name('create');
        Route::post('/', [SermonController::class, 'store'])
            ->middleware('permission:sermons.create')
            ->name('store');
        Route::get('/{sermon}/edit', [SermonController::class, 'edit'])
            ->middleware('permission:sermons.update')
            ->name('edit');
        Route::put('/{sermon}', [SermonController::class, 'update'])
            ->middleware('permission:sermons.update')
            ->name('update');
        Route::post('/{sermon}/publish', [SermonController::class, 'publish'])
            ->middleware('permission:sermons.publish')
            ->name('publish');
        Route::post('/{sermon}/deactivate', [SermonController::class, 'deactivate'])
            ->middleware('permission:sermons.deactivate')
            ->name('deactivate');
        Route::post('/{sermon}/send-links', [SermonController::class, 'sendLinks'])
            ->middleware('permission:sermons.send_sms')
            ->name('send-links');
        Route::delete('/{sermon}', [SermonController::class, 'destroy'])
            ->middleware('permission:sermons.delete')
            ->name('destroy');

        Route::get('/recipients/list', [SermonRecipientController::class, 'index'])
            ->middleware('permission:sermons.recipients.view')
            ->name('recipients.index');
        Route::post('/recipients/{recipient}/resend', [SermonRecipientController::class, 'resend'])
            ->middleware('permission:sermons.send_sms')
            ->name('recipients.resend');
        Route::post('/tokens/{token}/toggle', [SermonRecipientController::class, 'toggleToken'])
            ->middleware('permission:sermons.recipients.view')
            ->name('tokens.toggle');

        Route::get('/requests/list', [SermonRequestController::class, 'index'])
            ->middleware('permission:sermons.requests.view')
            ->name('requests.index');
        Route::post('/requests/{sermonRequest}/status', [SermonRequestController::class, 'changeStatus'])
            ->middleware('permission:sermons.requests.update')
            ->name('requests.change-status');
        Route::post('/requests/{sermonRequest}/respond', [SermonRequestController::class, 'respond'])
            ->middleware('permission:sermons.requests.respond')
            ->name('requests.respond');
    });


/*
|--------------------------------------------------------------------------
| Public sermon request from frontend contact page
|--------------------------------------------------------------------------
*/
Route::post('/sermon-requests/submit', [SermonRequestPublicController::class, 'store'])
    ->name('frontend.sermon-requests.submit');

/*
|--------------------------------------------------------------------------
| Public short secure sermon link
|--------------------------------------------------------------------------
| Register this route file near the bottom of routes/web.php so existing
| routes win first. Token is constrained to avoid matching common app paths.
| Example SMS link: https://domain.com/AbC123xYz9
*/
Route::get('/{sermonToken}', [PublicSermonController::class, 'show'])
    ->where('sermonToken', '^(?!admin$|api$|login$|logout$|register$|dashboard$|finance$|hall-bookings$|sermons$|mahubiri$|storage$|css$|js$|assets$)[A-Za-z0-9]{8,20}$')
    ->name('public.sermons.secure.show');
