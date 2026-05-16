<?php

use App\Http\Controllers\Admin\Cms\AnnouncementController;
use App\Http\Controllers\Admin\Cms\CmsDashboardController;
use App\Http\Controllers\Admin\Cms\FooterCenterDetailsController;
use App\Http\Controllers\Admin\Cms\GalleryController;
use App\Http\Controllers\Admin\Cms\HeroBannerController;
use App\Http\Controllers\Admin\Cms\HistoryController;
use App\Http\Controllers\Admin\Cms\HomepageBuilderController;
use App\Http\Controllers\Admin\Cms\NavigationSettingsController;
use App\Http\Controllers\Admin\Cms\PageController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('cms')->name('cms.')->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */
    Route::get('/dashboard', [CmsDashboardController::class, 'index'])
        ->middleware('permission:cms.dashboard.view')
        ->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Homepage Builder
    |--------------------------------------------------------------------------
    */
    Route::get('/homepage', [HomepageBuilderController::class, 'edit'])
        ->middleware('permission:cms.homepage.view')
        ->name('homepage.edit');

    Route::put('/homepage', [HomepageBuilderController::class, 'update'])
        ->middleware('permission:cms.homepage.update')
        ->name('homepage.update');

    /*
    |--------------------------------------------------------------------------
    | Hero Banners
    |--------------------------------------------------------------------------
    */
    Route::get('/heroes', [HeroBannerController::class, 'index'])
        ->middleware('permission:cms.heroes.view')
        ->name('heroes.index');

    Route::get('/heroes/create', [HeroBannerController::class, 'create'])
        ->middleware('permission:cms.heroes.create')
        ->name('heroes.create');

    Route::post('/heroes', [HeroBannerController::class, 'store'])
        ->middleware('permission:cms.heroes.create')
        ->name('heroes.store');

    Route::get('/heroes/{heroBanner}/edit', [HeroBannerController::class, 'edit'])
        ->middleware('permission:cms.heroes.update')
        ->name('heroes.edit');

    Route::put('/heroes/{heroBanner}', [HeroBannerController::class, 'update'])
        ->middleware('permission:cms.heroes.update')
        ->name('heroes.update');

    Route::delete('/heroes/{heroBanner}', [HeroBannerController::class, 'destroy'])
        ->middleware('permission:cms.heroes.delete')
        ->name('heroes.destroy');

    /*
    |--------------------------------------------------------------------------
    | Histories
    |--------------------------------------------------------------------------
    */
    Route::get('/histories', [HistoryController::class, 'index'])
        ->middleware('permission:cms.histories.view')
        ->name('histories.index');

    Route::get('/histories/create', [HistoryController::class, 'create'])
        ->middleware('permission:cms.histories.create')
        ->name('histories.create');

    Route::post('/histories', [HistoryController::class, 'store'])
        ->middleware('permission:cms.histories.create')
        ->name('histories.store');

    Route::get('/histories/{history}/edit', [HistoryController::class, 'edit'])
        ->middleware('permission:cms.histories.update')
        ->name('histories.edit');

    Route::put('/histories/{history}', [HistoryController::class, 'update'])
        ->middleware('permission:cms.histories.update')
        ->name('histories.update');

    Route::delete('/histories/{history}', [HistoryController::class, 'destroy'])
        ->middleware('permission:cms.histories.delete')
        ->name('histories.destroy');

    /*
    |--------------------------------------------------------------------------
    | Announcements
    |--------------------------------------------------------------------------
    */
    Route::get('/announcements', [AnnouncementController::class, 'index'])
        ->middleware('permission:cms.announcements.view')
        ->name('announcements.index');

    Route::get('/announcements/create', [AnnouncementController::class, 'create'])
        ->middleware('permission:cms.announcements.create')
        ->name('announcements.create');

    Route::post('/announcements', [AnnouncementController::class, 'store'])
        ->middleware('permission:cms.announcements.create')
        ->name('announcements.store');

    Route::get('/announcements/{announcement}/edit', [AnnouncementController::class, 'edit'])
        ->middleware('permission:cms.announcements.update')
        ->name('announcements.edit');

    Route::put('/announcements/{announcement}', [AnnouncementController::class, 'update'])
        ->middleware('permission:cms.announcements.update')
        ->name('announcements.update');

    Route::delete('/announcements/{announcement}', [AnnouncementController::class, 'destroy'])
        ->middleware('permission:cms.announcements.delete')
        ->name('announcements.destroy');

    /*
    |--------------------------------------------------------------------------
    | Pages
    |--------------------------------------------------------------------------
    */
    Route::get('/pages', [PageController::class, 'index'])
        ->middleware('permission:cms.pages.view')
        ->name('pages.index');

    Route::get('/pages/create', [PageController::class, 'create'])
        ->middleware('permission:cms.pages.create')
        ->name('pages.create');

    Route::post('/pages', [PageController::class, 'store'])
        ->middleware('permission:cms.pages.create')
        ->name('pages.store');

    Route::get('/pages/{page}/edit', [PageController::class, 'edit'])
        ->middleware('permission:cms.pages.update')
        ->name('pages.edit');

    Route::put('/pages/{page}', [PageController::class, 'update'])
        ->middleware('permission:cms.pages.update')
        ->name('pages.update');

    Route::delete('/pages/{page}', [PageController::class, 'destroy'])
        ->middleware('permission:cms.pages.delete')
        ->name('pages.destroy');

    /*
    |--------------------------------------------------------------------------
    | Galleries
    |--------------------------------------------------------------------------
    */
    Route::get('/galleries', [GalleryController::class, 'index'])
        ->middleware('permission:cms.galleries.view')
        ->name('galleries.index');

    Route::get('/galleries/create', [GalleryController::class, 'create'])
        ->middleware('permission:cms.galleries.create')
        ->name('galleries.create');

    Route::post('/galleries', [GalleryController::class, 'store'])
        ->middleware('permission:cms.galleries.create')
        ->name('galleries.store');

    Route::get('/galleries/{gallery}/edit', [GalleryController::class, 'edit'])
        ->middleware('permission:cms.galleries.update')
        ->name('galleries.edit');

    Route::put('/galleries/{gallery}', [GalleryController::class, 'update'])
        ->middleware('permission:cms.galleries.update')
        ->name('galleries.update');

    Route::delete('/galleries/{gallery}', [GalleryController::class, 'destroy'])
        ->middleware('permission:cms.galleries.delete')
        ->name('galleries.destroy');

    Route::post('/galleries/{gallery}/images', [GalleryController::class, 'storeImage'])
        ->middleware('permission:cms.galleries.update')
        ->name('galleries.images.store');

    Route::delete('/galleries/{gallery}/images/{image}', [GalleryController::class, 'destroyImage'])
        ->middleware('permission:cms.galleries.update')
        ->name('galleries.images.destroy');

    /*
    |--------------------------------------------------------------------------
    | Navigation Settings
    |--------------------------------------------------------------------------
    */
    Route::get('/navigation', [NavigationSettingsController::class, 'edit'])
        ->middleware('permission:cms.navigation.view')
        ->name('navigation.edit');

    Route::put('/navigation', [NavigationSettingsController::class, 'update'])
        ->middleware('permission:cms.navigation.update')
        ->name('navigation.update');

    /*
    |--------------------------------------------------------------------------
    | Footer & Center Details
    |--------------------------------------------------------------------------
    */
    Route::get('/footer-center-details', [FooterCenterDetailsController::class, 'edit'])
        ->middleware('permission:cms.footer.view')
        ->name('footer.edit');

    Route::put('/footer-center-details', [FooterCenterDetailsController::class, 'update'])
        ->middleware('permission:cms.footer.update')
        ->name('footer.update');
});