<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cms\UpdateHomepageBuilderRequest;
use App\Services\Audit\AuditLogService;
use App\Services\Cms\CmsMediaUploadService;
use App\Services\Cms\HomepageBuilderService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class HomepageBuilderController extends Controller
{
    public function __construct(
        protected HomepageBuilderService $homepageBuilderService,
        protected CmsMediaUploadService $mediaUploadService,
        protected AuditLogService $auditLogService
    ) {
    }

    public function edit(Request $request): View
    {
        abort_unless($request->user()?->can('cms.homepage.view'), 403);

        return view('admin.cms.homepage.edit', [
            'heroSettings' => $this->homepageBuilderService->heroSettings(),
            'homepageToggles' => $this->homepageBuilderService->homepageToggles(),
        ]);
    }

    public function update(UpdateHomepageBuilderRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($path = $this->mediaUploadService->store($request->file('video_file'), 'cms/hero')) {
            $data['video_url'] = $path;
        }

        if ($path = $this->mediaUploadService->store($request->file('poster_file'), 'cms/hero')) {
            $data['poster_url'] = $path;
        }

        if ($path = $this->mediaUploadService->store($request->file('image_file'), 'cms/hero')) {
            $data['image_url'] = $path;
        }

        if ($path = $this->mediaUploadService->store($request->file('mobile_image_file'), 'cms/hero')) {
            $data['mobile_image_url'] = $path;
        }

        $heroSettings = [
            'hero_badge' => $data['hero_badge'] ?? null,
            'enable_video' => (bool) ($data['enable_video'] ?? false),
            'video_url' => $data['video_url'] ?? null,
            'poster_url' => $data['poster_url'] ?? null,
            'image_url' => $data['image_url'] ?? null,
            'mobile_image_url' => $data['mobile_image_url'] ?? null,
            'overlay_opacity' => $data['overlay_opacity'] ?? '0.50',
            'cta_primary_text' => $data['cta_primary_text'] ?? null,
            'cta_primary_link' => $data['cta_primary_link'] ?? null,
            'cta_secondary_text' => $data['cta_secondary_text'] ?? null,
            'cta_secondary_link' => $data['cta_secondary_link'] ?? null,
        ];

        $homepageToggles = [
            'homepage.show_announcements' => (bool) ($data['homepage.show_announcements'] ?? false),
            'homepage.show_masses' => (bool) ($data['homepage.show_masses'] ?? false),
            'homepage.show_projects' => (bool) ($data['homepage.show_projects'] ?? false),
            'homepage.show_ministries' => (bool) ($data['homepage.show_ministries'] ?? false),
            'homepage.show_gallery' => (bool) ($data['homepage.show_gallery'] ?? false),
            'homepage.show_leadership' => (bool) ($data['homepage.show_leadership'] ?? false),
            'homepage.show_contact' => (bool) ($data['homepage.show_contact'] ?? false),
        ];

        $this->homepageBuilderService->updateHeroSettings($heroSettings);
        $this->homepageBuilderService->updateHomepageToggles($homepageToggles);

        $this->auditLogService->log([
            'user' => $request->user(),
            'event' => 'updated',
            'module' => 'cms',
            'action' => 'Homepage builder updated',
            'subject_type' => 'cms_homepage',
            'subject_id' => null,
            'subject_label' => 'Homepage Builder',
            'description' => 'Homepage builder settings updated',
            'new_values' => [
                'hero_settings' => $heroSettings,
                'homepage_toggles' => $homepageToggles,
            ],
            'risk_level' => 'medium',
        ]);

        return back()->with('success', db_trans('homepage_builder_updated_successfully'));
    }
}
