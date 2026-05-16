<?php

namespace App\Http\Controllers\Admin\SystemConfig;

use App\Http\Controllers\Controller;
use App\Http\Requests\SystemConfig\UpdateGeneralSettingsRequest;
use App\Services\Audit\AuditLogService;
use App\Services\SystemConfig\SiteSettingsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GeneralSettingController extends Controller
{
    public function __construct(
        protected SiteSettingsService $siteSettingsService,
        protected AuditLogService $auditLogService
    ) {
    }

    public function edit(Request $request): View
    {
        abort_unless($request->user()?->can('system.config.general.view'), 403);

        return view('admin.system-config.general', [
            'settings' => [
                'site_name' => $this->siteSettingsService->get('site.name', config('app.name')),
                'site_tagline' => $this->siteSettingsService->get('site.tagline'),
                'site_description' => $this->siteSettingsService->get('site.description'),
                'site_image' => $this->siteSettingsService->get('site.image'),
                'default_locale' => $this->siteSettingsService->get('site.default_locale', config('app.locale')),
                'fallback_locale' => $this->siteSettingsService->get('site.fallback_locale', config('app.fallback_locale')),
                'public_url' => $this->siteSettingsService->get('site.public_url', config('app.url')),
            ],
        ]);
    }

    public function update(UpdateGeneralSettingsRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $oldValues = [
            'site.name' => $this->siteSettingsService->get('site.name'),
            'site.tagline' => $this->siteSettingsService->get('site.tagline'),
            'site.description' => $this->siteSettingsService->get('site.description'),
            'site.image' => $this->siteSettingsService->get('site.image'),
            'site.default_locale' => $this->siteSettingsService->get('site.default_locale'),
            'site.fallback_locale' => $this->siteSettingsService->get('site.fallback_locale'),
            'site.public_url' => $this->siteSettingsService->get('site.public_url'),
        ];

        $this->siteSettingsService->setMany([
            'site.name' => ['value' => $data['site_name'], 'type' => 'string', 'is_public' => true, 'sort_order' => 10],
            'site.tagline' => ['value' => $data['site_tagline'] ?? null, 'type' => 'string', 'is_public' => true, 'sort_order' => 20],
            'site.description' => ['value' => $data['site_description'] ?? null, 'type' => 'string', 'is_public' => true, 'sort_order' => 30],
            'site.image' => ['value' => $data['site_image'] ?? null, 'type' => 'string', 'is_public' => true, 'sort_order' => 40],
            'site.default_locale' => ['value' => $data['default_locale'], 'type' => 'string', 'is_public' => false, 'sort_order' => 50],
            'site.fallback_locale' => ['value' => $data['fallback_locale'], 'type' => 'string', 'is_public' => false, 'sort_order' => 60],
            'site.public_url' => ['value' => $data['public_url'] ?? null, 'type' => 'string', 'is_public' => true, 'sort_order' => 70],
        ], 'site');

        $this->auditLogService->log([
            'user' => $request->user(),
            'event' => 'updated',
            'module' => 'system_config',
            'action' => 'General settings updated',
            'subject_type' => 'system_config',
            'subject_id' => null,
            'subject_label' => 'General Settings',
            'description' => 'System general settings updated',
            'old_values' => $oldValues,
            'new_values' => [
                'site.name' => $data['site_name'],
                'site.tagline' => $data['site_tagline'] ?? null,
                'site.description' => $data['site_description'] ?? null,
                'site.image' => $data['site_image'] ?? null,
                'site.default_locale' => $data['default_locale'],
                'site.fallback_locale' => $data['fallback_locale'],
                'site.public_url' => $data['public_url'] ?? null,
            ],
            'risk_level' => 'medium',
        ]);

        return back()->with('success', db_trans('general_settings_updated_successfully'));
    }
}