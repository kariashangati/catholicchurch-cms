<?php

use App\Http\Controllers\Admin\Access\AccessDashboardController;
use App\Http\Controllers\Admin\Access\AccessNotificationTemplateController;
use App\Http\Controllers\Admin\Access\AdminUserController;
use App\Http\Controllers\Admin\Access\PermissionController;
use App\Http\Controllers\Admin\Access\RoleController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('system-access')->name('system-access.')->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Access Dashboard
    |--------------------------------------------------------------------------
    */
    Route::get('/dashboard', [AccessDashboardController::class, 'index'])
        ->middleware('permission:access.dashboard.view')
        ->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Admin Users
    |--------------------------------------------------------------------------
    */
    Route::get('/users', [AdminUserController::class, 'index'])
        ->middleware('permission:access.users.view')
        ->name('users.index');

    Route::get('/users/create', [AdminUserController::class, 'create'])
        ->middleware('permission:access.users.create')
        ->name('users.create');

    Route::post('/users', [AdminUserController::class, 'store'])
        ->middleware('permission:access.users.create')
        ->name('users.store');

    Route::get('/users/{user}', [AdminUserController::class, 'show'])
        ->middleware('permission:access.users.profile.view')
        ->name('users.show');

    Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])
        ->middleware('permission:access.users.update')
        ->name('users.edit');

    Route::put('/users/{user}', [AdminUserController::class, 'update'])
        ->middleware('permission:access.users.update')
        ->name('users.update');

    Route::patch('/users/{user}/activate', [AdminUserController::class, 'activate'])
        ->middleware('permission:access.users.activate')
        ->name('users.activate');

    Route::patch('/users/{user}/deactivate', [AdminUserController::class, 'deactivate'])
        ->middleware('permission:access.users.deactivate')
        ->name('users.deactivate');

    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])
        ->middleware('permission:access.users.delete')
        ->name('users.destroy');

    /*
    |--------------------------------------------------------------------------
    | Admin User AJAX helpers
    |--------------------------------------------------------------------------
    */
    Route::get('/ajax/jumuiyas', [AdminUserController::class, 'jumuiyas'])
        ->middleware('permission:access.users.create|access.users.update')
        ->name('ajax.jumuiyas');

    Route::get('/ajax/members', [AdminUserController::class, 'members'])
        ->middleware('permission:access.users.create|access.users.update')
        ->name('ajax.members');

    /*
    |--------------------------------------------------------------------------
    | Roles
    |--------------------------------------------------------------------------
    */
    Route::get('/roles', [RoleController::class, 'index'])
        ->middleware('permission:access.roles.view')
        ->name('roles.index');

    Route::get('/roles/create', [RoleController::class, 'create'])
        ->middleware('permission:access.roles.create')
        ->name('roles.create');

    Route::post('/roles', [RoleController::class, 'store'])
        ->middleware('permission:access.roles.create')
        ->name('roles.store');

    Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])
        ->middleware('permission:access.roles.update')
        ->name('roles.edit');

    Route::put('/roles/{role}', [RoleController::class, 'update'])
        ->middleware('permission:access.roles.update')
        ->name('roles.update');

    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])
        ->middleware('permission:access.roles.delete')
        ->name('roles.destroy');

    /*
    |--------------------------------------------------------------------------
    | Permissions
    |--------------------------------------------------------------------------
    */
    Route::get('/permissions', [PermissionController::class, 'index'])
        ->middleware('permission:access.permissions.view')
        ->name('permissions.index');

    Route::get('/permissions/create', [PermissionController::class, 'create'])
        ->middleware('permission:access.permissions.create')
        ->name('permissions.create');

    Route::post('/permissions', [PermissionController::class, 'store'])
        ->middleware('permission:access.permissions.create')
        ->name('permissions.store');

    Route::get('/permissions/{permission}/edit', [PermissionController::class, 'edit'])
        ->middleware('permission:access.permissions.update')
        ->name('permissions.edit');

    Route::put('/permissions/{permission}', [PermissionController::class, 'update'])
        ->middleware('permission:access.permissions.update')
        ->name('permissions.update');

    Route::patch('/permissions/{permission}/toggle', [PermissionController::class, 'toggle'])
        ->middleware('permission:access.permissions.toggle')
        ->name('permissions.toggle');

    Route::delete('/permissions/{permission}', [PermissionController::class, 'destroy'])
        ->middleware('permission:access.permissions.delete')
        ->name('permissions.destroy');

    /*
    |--------------------------------------------------------------------------
    | Access Notification Templates
    |--------------------------------------------------------------------------
    */
    Route::get('/templates', [AccessNotificationTemplateController::class, 'index'])
        ->middleware('permission:access.templates.view')
        ->name('templates.index');

    Route::put('/templates', [AccessNotificationTemplateController::class, 'update'])
        ->middleware('permission:access.templates.update')
        ->name('templates.update');
});