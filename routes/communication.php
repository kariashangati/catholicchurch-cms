<?php

use App\Http\Controllers\Admin\Communication\CommunicationApprovalController;
use App\Http\Controllers\Admin\Communication\CommunicationAutomationController;
use App\Http\Controllers\Admin\Communication\CommunicationBalanceController;
use App\Http\Controllers\Admin\Communication\CommunicationControlSettingsController;
use App\Http\Controllers\Admin\Communication\CommunicationDashboardController;
use App\Http\Controllers\Admin\Communication\CommunicationLogController;
use App\Http\Controllers\Admin\Communication\CommunicationPreferenceController;
use App\Http\Controllers\Admin\Communication\CommunicationScheduleController;
use App\Http\Controllers\Admin\Communication\CommunicationSmsSettingsController;
use App\Http\Controllers\Admin\Communication\CommunicationTemplateController;
use App\Http\Controllers\Admin\Communication\SmsMessageController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])
    ->prefix('admin/communication')
    ->name('admin.communication.')
    ->group(function () {
        Route::get('/dashboard', [CommunicationDashboardController::class, 'index'])
            ->middleware('permission:communication.view')
            ->name('dashboard');

        Route::get('/sms', [SmsMessageController::class, 'index'])
            ->middleware('permission:communication.send')
            ->name('sms.index');
        Route::get('/sms/create', [SmsMessageController::class, 'create'])
            ->middleware('permission:communication.send')
            ->name('sms.create');
        Route::post('/sms/preview', [SmsMessageController::class, 'preview'])
            ->middleware('permission:communication.send')
            ->name('sms.preview');
        Route::post('/sms/store', [SmsMessageController::class, 'store'])
            ->middleware('permission:communication.send')
            ->name('sms.store');
        Route::get('/sms/templates/{template}', [SmsMessageController::class, 'template'])
            ->middleware('permission:communication.send')
            ->name('sms.templates.show')
            ->whereNumber('template');
        Route::get('/sms/settings', [CommunicationSmsSettingsController::class, 'edit'])
            ->middleware('permission:communication.manage_controls')
            ->name('sms.settings.edit');
        Route::put('/sms/settings', [CommunicationSmsSettingsController::class, 'update'])
            ->middleware('permission:communication.manage_controls')
            ->name('sms.settings.update');

        Route::post('/templates/preview', [CommunicationTemplateController::class, 'preview'])
            ->middleware('permission:communication.manage_templates')
            ->name('templates.preview');
        Route::resource('templates', CommunicationTemplateController::class)
            ->middleware('permission:communication.manage_templates');

        Route::get('automations/schema/{eventKey}', [CommunicationAutomationController::class, 'schema'])
            ->middleware('permission:communication.manage_automations')
            ->name('automations.schema');
        Route::patch('automations/{automation}/toggle', [CommunicationAutomationController::class, 'toggle'])
            ->middleware('permission:communication.manage_automations')
            ->name('automations.toggle');
        Route::resource('automations', CommunicationAutomationController::class)
            ->except(['show'])
            ->middleware('permission:communication.manage_automations');

        Route::get('/schedules', [CommunicationScheduleController::class, 'index'])
            ->middleware('permission:communication.schedule')
            ->name('schedules.index');
        Route::post('/campaigns/{campaign}/schedule', [CommunicationScheduleController::class, 'schedule'])
            ->middleware('permission:communication.schedule')
            ->name('campaigns.schedule');
        Route::patch('/campaigns/{campaign}/reschedule', [CommunicationScheduleController::class, 'reschedule'])
            ->middleware('permission:communication.schedule')
            ->name('campaigns.reschedule');
        Route::patch('/campaigns/{campaign}/cancel-schedule', [CommunicationScheduleController::class, 'cancel'])
            ->middleware('permission:communication.schedule')
            ->name('campaigns.cancel_schedule');

        Route::get('preferences', [CommunicationPreferenceController::class, 'index'])
            ->middleware('permission:communication.manage_preferences')
            ->name('preferences.index');
        Route::get('preferences/{member}/edit', [CommunicationPreferenceController::class, 'edit'])
            ->middleware('permission:communication.manage_preferences')
            ->name('preferences.edit');
        Route::put('preferences/{member}', [CommunicationPreferenceController::class, 'update'])
            ->middleware('permission:communication.manage_preferences')
            ->name('preferences.update');
        Route::post('preferences/{member}/opt-out', [CommunicationPreferenceController::class, 'optOut'])
            ->middleware('permission:communication.manage_preferences')
            ->name('preferences.opt-out');
        Route::post('preferences/{member}/opt-in', [CommunicationPreferenceController::class, 'optIn'])
            ->middleware('permission:communication.manage_preferences')
            ->name('preferences.opt-in');

        Route::get('/approvals', [CommunicationApprovalController::class, 'index'])
            ->middleware('permission:communication.approve_campaigns')
            ->name('approvals.index');
        Route::post('/approvals/{campaign}/approve', [CommunicationApprovalController::class, 'approve'])
            ->middleware('permission:communication.approve_campaigns')
            ->name('approvals.approve');
        Route::post('/approvals/{campaign}/reject', [CommunicationApprovalController::class, 'reject'])
            ->middleware('permission:communication.approve_campaigns')
            ->name('approvals.reject');
        Route::post('/approvals/{campaign}/cancel', [CommunicationApprovalController::class, 'cancel'])
            ->middleware('permission:communication.cancel_campaigns')
            ->name('approvals.cancel');
        Route::post('/approvals/{campaign}/retry-failed', [CommunicationApprovalController::class, 'retryFailed'])
            ->middleware('permission:communication.retry_failed')
            ->name('approvals.retry_failed');

        Route::get('/controls', [CommunicationControlSettingsController::class, 'edit'])
            ->middleware('permission:communication.manage_controls')
            ->name('controls.edit');
        Route::put('/controls', [CommunicationControlSettingsController::class, 'update'])
            ->middleware('permission:communication.manage_controls')
            ->name('controls.update');

        Route::get('/logs', [CommunicationLogController::class, 'index'])
            ->middleware('permission:communication.view_logs')
            ->name('logs.index');
        Route::get('/balance', [CommunicationBalanceController::class, 'index'])
            ->middleware('permission:communication.manage_balance')
            ->name('balance.index');
    });
