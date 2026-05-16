<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cms\UpdateNavigationSettingsRequest;
use App\Services\Audit\AuditLogService;
use App\Services\Cms\NavigationSettingsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NavigationSettingsController extends Controller
{
    public function __construct(
        protected NavigationSettingsService $navigationSettingsService,
        protected AuditLogService $auditLogService
    ) {
    }

    public function edit(Request $request): View
    {
        abort_unless($request->user()?->can('cms.navigation.view'), 403);

        return view('admin.cms.navigation.edit', [
            'settings' => $this->navigationSettingsService->all(),
        ]);
    }

    public function update(UpdateNavigationSettingsRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $this->navigationSettingsService->update($data);

        $this->auditLogService->log([
            'user' => $request->user(),
            'event' => 'updated',
            'module' => 'cms',
            'action' => 'Navigation settings updated',
            'subject_type' => 'cms_navigation',
            'subject_id' => null,
            'subject_label' => 'Navigation Settings',
            'description' => 'Navigation visibility settings updated',
            'new_values' => $data,
            'risk_level' => 'low',
        ]);

        return back()->with('success', db_trans('navigation_settings_updated_successfully'));
    }
}