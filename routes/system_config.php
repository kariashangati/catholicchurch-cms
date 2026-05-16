<?php

use App\Http\Controllers\Admin\SystemConfig\BrandingSettingController;
use App\Http\Controllers\Admin\SystemConfig\CommunicationConfigurationController;
use App\Http\Controllers\Admin\SystemConfig\EnvironmentSettingController;
use App\Http\Controllers\Admin\SystemConfig\GeneralSettingController;
use App\Http\Controllers\Admin\SystemConfig\MaintenanceSettingController;
use App\Http\Controllers\Admin\SystemConfig\SystemConfigDashboardController;
use App\Http\Controllers\Admin\SystemConfig\SystemInformationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('system-config')->name('system-config.')->group(function () {
    Route::get('/dashboard', [SystemConfigDashboardController::class, 'index'])
        ->middleware('permission:system.config.dashboard.view')
        ->name('dashboard');

    Route::get('/general', [GeneralSettingController::class, 'edit'])
        ->middleware('permission:system.config.general.view')
        ->name('general.edit');

    Route::put('/general', [GeneralSettingController::class, 'update'])
        ->middleware('permission:system.config.general.update')
        ->name('general.update');

    Route::get('/branding', [BrandingSettingController::class, 'edit'])
        ->middleware('permission:system.config.branding.view')
        ->name('branding.edit');

    Route::post('/branding', [BrandingSettingController::class, 'update'])
        ->middleware('permission:system.config.branding.update')
        ->name('branding.update');

    Route::get('/maintenance', [MaintenanceSettingController::class, 'edit'])
        ->middleware('permission:system.config.maintenance.view')
        ->name('maintenance.edit');

    Route::put('/maintenance', [MaintenanceSettingController::class, 'update'])
        ->middleware('permission:system.config.maintenance.update')
        ->name('maintenance.update');

    Route::post('/maintenance/laravel-up', [MaintenanceSettingController::class, 'forceLaravelUp'])
        ->middleware('permission:system.config.maintenance.update')
        ->name('maintenance.laravel-up');

    Route::get('/environment', [EnvironmentSettingController::class, 'edit'])
        ->middleware('permission:system.config.environment.view')
        ->name('environment.edit');

    Route::put('/environment', [EnvironmentSettingController::class, 'update'])
        ->middleware('permission:system.config.environment.update')
        ->name('environment.update');

    Route::get('/system-information', [SystemInformationController::class, 'index'])
        ->middleware('permission:system.config.systeminfo.view')
        ->name('system-information.index');

    Route::get('/communication', [CommunicationConfigurationController::class, 'edit'])
        ->middleware('permission:system.config.communication.view')
        ->name('communication.edit');

    Route::put('/communication', [CommunicationConfigurationController::class, 'update'])
        ->middleware('permission:system.config.communication.update')
        ->name('communication.update');
});
