<?php

namespace App\Http\Controllers\Admin\SystemConfig;

use App\Http\Controllers\Controller;
use App\Http\Requests\SystemConfig\UpdateBrandingSettingsRequest;
use App\Services\Audit\AuditLogService;
use App\Services\SystemConfig\SiteSettingsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BrandingSettingController extends Controller
{
    public function __construct(
        protected SiteSettingsService $siteSettingsService,
        protected AuditLogService $auditLogService
    ) {
    }

    public function edit(Request $request): View
    {
        abort_unless($request->user()?->can('system.config.branding.view'), 403);

        return view('admin.system-config.branding', [
            'settings' => [
                'site_logo' => $this->siteSettingsService->get('site.logo'),
                'site_favicon' => $this->siteSettingsService->get('site.favicon'),
                'site_image' => $this->siteSettingsService->get('site.image'),
            ],
        ]);
    }

    public function update(UpdateBrandingSettingsRequest $request): RedirectResponse
    {
        $oldValues = [
            'site.logo' => $this->siteSettingsService->get('site.logo'),
            'site.favicon' => $this->siteSettingsService->get('site.favicon'),
            'site.image' => $this->siteSettingsService->get('site.image'),
        ];

        if ($request->boolean('remove_site_logo')) {
            $this->siteSettingsService->removeAsset('site.logo');
        }

        if ($request->boolean('remove_site_favicon')) {
            $this->siteSettingsService->removeAsset('site.favicon');
        }

        if ($request->boolean('remove_site_image')) {
            $this->siteSettingsService->removeAsset('site.image');
        }

        if ($request->hasFile('site_logo')) {
            $this->siteSettingsService->uploadAndSet(
                'site.logo',
                $request->file('site_logo'),
                'uploads/frontend/logo',
                'site',
                true,
                100
            );
        }

        if ($request->hasFile('site_favicon')) {
            $this->siteSettingsService->uploadAndSet(
                'site.favicon',
                $request->file('site_favicon'),
                'uploads/frontend/favicon',
                'site',
                true,
                110
            );
        }

        if ($request->hasFile('site_image')) {
            $this->siteSettingsService->uploadAndSet(
                'site.image',
                $request->file('site_image'),
                'uploads/frontend/image',
                'site',
                true,
                120
            );
        }

        $this->auditLogService->log([
            'user' => $request->user(),
            'event' => 'updated',
            'module' => 'system_config',
            'action' => 'Branding settings updated',
            'subject_type' => 'system_config',
            'subject_id' => null,
            'subject_label' => 'Branding Settings',
            'description' => 'System branding settings updated',
            'old_values' => $oldValues,
            'new_values' => [
                'site.logo' => $this->siteSettingsService->get('site.logo'),
                'site.favicon' => $this->siteSettingsService->get('site.favicon'),
                'site.image' => $this->siteSettingsService->get('site.image'),
            ],
            'risk_level' => 'medium',
        ]);

        return back()->with('success', db_trans('branding_settings_updated_successfully'));
    }
}