<?php

namespace App\Http\Controllers\Admin\SystemConfig;

use App\Http\Controllers\Controller;
use App\Http\Requests\SystemConfig\UpdateMaintenanceSettingsRequest;
use App\Services\Audit\AuditLogService;
use App\Services\SystemConfig\MaintenanceModeService;
use App\Services\SystemConfig\SiteSettingsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MaintenanceSettingController extends Controller
{
    public function __construct(
        protected MaintenanceModeService $maintenanceModeService,
        protected SiteSettingsService $siteSettingsService,
        protected AuditLogService $auditLogService
    ) {
    }

    public function edit(Request $request): View
    {
        abort_unless($request->user()?->can('system.config.maintenance.view'), 403);

        $savedRoutes = $this->siteSettingsService->maintenanceRoutes();
        $suggestedRoutes = collect([
            'home',
            'projects.index',
            'contact.index',
            'dashboard',
            'finance.contributions.dashboard',
            'finance.contributions.cash.index',
            'finance.contributions.bank.index',
            'finance.contributions.bulk.index',
            'reports.index',
            'receipts.dashboard',
            'admin.communication.dashboard',
            'system-access.dashboard',
        ])->merge(array_keys($savedRoutes))->unique()->values()->all();

        return view('admin.system-config.maintenance', [
            'global' => $this->maintenanceModeService->globalSettings(),
            'routes' => $savedRoutes,
            'suggestedRoutes' => $suggestedRoutes,
        ]);
    }

    public function update(UpdateMaintenanceSettingsRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $oldValues = [
            'global' => $this->maintenanceModeService->globalSettings(),
            'routes' => $this->siteSettingsService->maintenanceRoutes(),
        ];

        $useLaravelDown = (bool) ($data['use_laravel_down'] ?? false);
        $title = $data['global_title'] ?? db_trans('maintenance_mode');
        $message = $data['global_message'] ?? db_trans('system_is_currently_under_maintenance');

        if ((bool) $data['global_enabled']) {
            $this->maintenanceModeService->enableGlobal($title, $message, $useLaravelDown);
        } else {
            $this->maintenanceModeService->disableGlobal(true);
            $this->siteSettingsService->set('maintenance.global.title', $title, 'string', 'maintenance');
            $this->siteSettingsService->set('maintenance.global.message', $message, 'string', 'maintenance');
        }

        foreach ($data['routes'] ?? [] as $route) {
            $name = trim((string) ($route['name'] ?? ''));

            if ($name === '') {
                continue;
            }

            $this->maintenanceModeService->setRouteMaintenance(
                $name,
                (bool) ($route['enabled'] ?? false),
                $route['title'] ?? db_trans('maintenance_mode'),
                $route['message'] ?? db_trans('this_page_is_under_maintenance')
            );
        }

        $this->auditLogService->log([
            'user' => $request->user(),
            'event' => 'updated',
            'module' => 'system_config',
            'action' => 'Maintenance settings updated',
            'subject_type' => 'system_config',
            'subject_id' => null,
            'subject_label' => 'Maintenance Settings',
            'description' => 'System maintenance settings updated',
            'old_values' => $oldValues,
            'new_values' => [
                'global' => $this->maintenanceModeService->globalSettings(),
                'routes' => $this->siteSettingsService->maintenanceRoutes(),
            ],
            'risk_level' => 'high',
        ]);

        return back()->with('success', db_trans('maintenance_settings_updated_successfully'));
    }

    public function forceLaravelUp(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('system.config.maintenance.update'), 403);

        $this->maintenanceModeService->turnLaravelUp();
        $this->maintenanceModeService->disableGlobal(false);

        $this->auditLogService->log([
            'user' => $request->user(),
            'event' => 'updated',
            'module' => 'system_config',
            'action' => 'Laravel maintenance mode disabled from UI',
            'subject_type' => 'system_config',
            'subject_id' => null,
            'subject_label' => 'Maintenance Settings',
            'description' => 'Laravel maintenance mode disabled from system configuration UI',
            'old_values' => [],
            'new_values' => $this->maintenanceModeService->globalSettings(),
            'risk_level' => 'high',
        ]);

        return back()->with('success', db_trans('maintenance_mode_disabled_successfully'));
    }
}
