<?php

namespace App\Http\Controllers\Admin\SystemConfig;

use App\Http\Controllers\Controller;
use App\Http\Requests\SystemConfig\UpdateCommunicationSettingsRequest;
use App\Services\Audit\AuditLogService;
use App\Services\SystemConfig\CommunicationConfigurationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommunicationConfigurationController extends Controller
{
    public function __construct(
        protected CommunicationConfigurationService $communicationConfigurationService,
        protected AuditLogService $auditLogService
    ) {
    }

    public function edit(Request $request): View
    {
        abort_unless($request->user()?->can('system.config.communication.view'), 403);

        return view('admin.system-config.communication', [
            'mailSettings' => $this->communicationConfigurationService->mailSettings(),
            'smsSettings' => $this->communicationConfigurationService->smsSettings(),
            'smsBalance' => $this->communicationConfigurationService->smsBalance(),
        ]);
    }

    public function update(UpdateCommunicationSettingsRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $mailKeys = [
            'MAIL_MAILER',
            'MAIL_HOST',
            'MAIL_PORT',
            'MAIL_USERNAME',
            'MAIL_PASSWORD',
            'MAIL_FROM_ADDRESS',
            'MAIL_FROM_NAME',
        ];

        $smsKeys = [
            'BONGO_LIVE_KEY',
            'BONGO_LIVE_SECRET',
            'BONGO_SENDER_ID',
            'BEEM_SMS_SEND_URL',
            'BEEM_SMS_BALANCE_URL',
        ];

        $mailPayload = array_intersect_key($data, array_flip($mailKeys));
        $smsPayload = array_intersect_key($data, array_flip($smsKeys));

        if (! empty($mailPayload)) {
            $this->communicationConfigurationService->updateMailSettings($mailPayload);
        }

        if (! empty($smsPayload)) {
            $this->communicationConfigurationService->updateSmsSettings($smsPayload);
        }

        $this->auditLogService->log([
            'user' => $request->user(),
            'event' => 'updated',
            'module' => 'system_config',
            'action' => 'Communication configuration updated',
            'subject_type' => 'system_config',
            'subject_id' => null,
            'subject_label' => 'Communication Configuration',
            'description' => 'Mail and SMS environment configuration updated',
            'new_values' => array_keys($data),
            'risk_level' => 'critical',
        ]);

        return back()->with('success', db_trans('communication_configuration_updated_successfully'));
    }
}