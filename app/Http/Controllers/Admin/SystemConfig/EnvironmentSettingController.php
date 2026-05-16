<?php

namespace App\Http\Controllers\Admin\SystemConfig;

use App\Http\Controllers\Controller;
use App\Http\Requests\SystemConfig\UpdateEnvironmentSettingsRequest;
use App\Services\Audit\AuditLogService;
use App\Services\SystemConfig\EnvironmentSettingsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EnvironmentSettingController extends Controller
{
    public function __construct(
        protected EnvironmentSettingsService $environmentSettingsService,
        protected AuditLogService $auditLogService
    ) {
    }

    public function edit(Request $request): View
    {
        abort_unless($request->user()?->can('system.config.environment.view'), 403);

        return view('admin.system-config.environment', [
            'groups' => $this->environmentSettingsService->grouped(),
            'editableKeys' => $this->environmentSettingsService->editableKeys(),
            'maskedKeys' => $this->environmentSettingsService->maskedKeys(),
        ]);
    }

    public function update(UpdateEnvironmentSettingsRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $oldValues = [];
        $all = $this->environmentSettingsService->all();

        foreach (array_keys($data) as $key) {
            $oldValues[$key] = $all[$key] ?? null;
        }

        $this->environmentSettingsService->updateMany($data);

        $this->auditLogService->log([
            'user' => $request->user(),
            'event' => 'updated',
            'module' => 'system_config',
            'action' => 'Environment settings updated',
            'subject_type' => 'system_config',
            'subject_id' => null,
            'subject_label' => 'Environment Settings',
            'description' => 'Editable environment settings updated',
            'old_values' => $oldValues,
            'new_values' => $data,
            'risk_level' => 'critical',
        ]);

        return back()->with('success', db_trans('environment_settings_updated_successfully'));
    }
}