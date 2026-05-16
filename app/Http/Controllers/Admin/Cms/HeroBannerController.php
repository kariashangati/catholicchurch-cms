<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cms\StoreHeroBannerRequest;
use App\Http\Requests\Cms\UpdateHeroBannerRequest;
use App\Models\HeroBanner;
use App\Services\Audit\AuditLogService;
use App\Services\Cms\CmsMediaUploadService;
use App\Services\Cms\HeroBannerService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class HeroBannerController extends Controller
{
    public function __construct(
        protected HeroBannerService $heroBannerService,
        protected CmsMediaUploadService $mediaUploadService,
        protected AuditLogService $auditLogService
    ) {
    }

    public function index(Request $request): View
    {
        abort_unless($request->user()?->can('cms.heroes.view'), 403);

        return view('admin.cms.heroes.index', [
            'heroBanners' => $this->heroBannerService->paginated(),
        ]);
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()?->can('cms.heroes.create'), 403);

        return view('admin.cms.heroes.create');
    }

    public function store(StoreHeroBannerRequest $request): RedirectResponse
    {
        $data = $this->withUploadedMedia($request->validated(), $request);
        $heroBanner = $this->heroBannerService->create($data);

        $this->auditLogService->log([
            'user' => $request->user(),
            'event' => 'created',
            'module' => 'cms',
            'action' => 'Hero banner created',
            'subject' => $heroBanner,
            'subject_label' => $heroBanner->title ?: db_trans('hero_banner'),
            'description' => 'Hero banner created',
            'new_values' => $heroBanner->toArray(),
            'risk_level' => 'low',
        ]);

        return redirect()->route('cms.heroes.index')
            ->with('success', db_trans('hero_banner_created_successfully'));
    }

    public function edit(Request $request, HeroBanner $heroBanner): View
    {
        abort_unless($request->user()?->can('cms.heroes.update'), 403);

        return view('admin.cms.heroes.edit', compact('heroBanner'));
    }

    public function update(UpdateHeroBannerRequest $request, HeroBanner $heroBanner): RedirectResponse
    {
        $oldValues = $heroBanner->toArray();
        $data = $this->withUploadedMedia($request->validated(), $request);
        $heroBanner = $this->heroBannerService->update($heroBanner, $data);

        $this->auditLogService->log([
            'user' => $request->user(),
            'event' => 'updated',
            'module' => 'cms',
            'action' => 'Hero banner updated',
            'subject' => $heroBanner,
            'subject_label' => $heroBanner->title ?: db_trans('hero_banner'),
            'description' => 'Hero banner updated',
            'old_values' => $oldValues,
            'new_values' => $heroBanner->toArray(),
            'risk_level' => 'low',
        ]);

        return redirect()->route('cms.heroes.index')
            ->with('success', db_trans('hero_banner_updated_successfully'));
    }

    public function destroy(Request $request, HeroBanner $heroBanner): RedirectResponse
    {
        abort_unless($request->user()?->can('cms.heroes.delete'), 403);

        $oldValues = $heroBanner->toArray();
        $title = $heroBanner->title;

        $this->heroBannerService->delete($heroBanner);

        $this->auditLogService->log([
            'user' => $request->user(),
            'event' => 'deleted',
            'module' => 'cms',
            'action' => 'Hero banner deleted',
            'subject_type' => HeroBanner::class,
            'subject_id' => $oldValues['id'] ?? null,
            'subject_label' => $title,
            'description' => 'Hero banner deleted',
            'old_values' => $oldValues,
            'risk_level' => 'medium',
        ]);

        return redirect()->route('cms.heroes.index')
            ->with('success', db_trans('hero_banner_deleted_successfully'));
    }

    protected function withUploadedMedia(array $data, Request $request): array
    {
        if ($path = $this->mediaUploadService->store($request->file('background_file'), 'cms/heroes')) {
            $data['background_value'] = $path;
        }

        if ($path = $this->mediaUploadService->store($request->file('poster_image_file'), 'cms/heroes')) {
            $data['poster_image'] = $path;
        }

        unset($data['background_file'], $data['poster_image_file']);

        return $data;
    }
}
