<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cms\StoreGalleryImageRequest;
use App\Http\Requests\Cms\StoreGalleryRequest;
use App\Http\Requests\Cms\UpdateGalleryRequest;
use App\Models\Gallery;
use App\Models\GalleryImage;
use App\Services\Audit\AuditLogService;
use App\Services\Cms\CmsMediaUploadService;
use App\Services\Cms\GalleryContentService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    public function __construct(
        protected GalleryContentService $galleryContentService,
        protected CmsMediaUploadService $mediaUploadService,
        protected AuditLogService $auditLogService
    ) {
    }

    public function index(Request $request): View
    {
        abort_unless($request->user()?->can('cms.galleries.view'), 403);

        return view('admin.cms.galleries.index', [
            'galleries' => $this->galleryContentService->paginated(),
        ]);
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()?->can('cms.galleries.create'), 403);

        return view('admin.cms.galleries.create');
    }

    public function store(StoreGalleryRequest $request): RedirectResponse
    {
        $data = $this->withUploadedCover($request->validated(), $request);
        $gallery = $this->galleryContentService->createGallery($data);

        $this->auditLogService->log([
            'user' => $request->user(),
            'event' => 'created',
            'module' => 'cms',
            'action' => 'Gallery created',
            'subject' => $gallery,
            'subject_label' => $gallery->title,
            'description' => 'Gallery created',
            'new_values' => $gallery->toArray(),
            'risk_level' => 'low',
        ]);

        return redirect()->route('cms.galleries.index')
            ->with('success', db_trans('gallery_created_successfully'));
    }

    public function edit(Request $request, Gallery $gallery): View
    {
        abort_unless($request->user()?->can('cms.galleries.update'), 403);

        $gallery->load(['images' => fn ($query) => $query->orderBy('display_order')]);

        return view('admin.cms.galleries.edit', compact('gallery'));
    }

    public function update(UpdateGalleryRequest $request, Gallery $gallery): RedirectResponse
    {
        $oldValues = $gallery->toArray();
        $data = $this->withUploadedCover($request->validated(), $request);
        $gallery = $this->galleryContentService->updateGallery($gallery, $data);

        $this->auditLogService->log([
            'user' => $request->user(),
            'event' => 'updated',
            'module' => 'cms',
            'action' => 'Gallery updated',
            'subject' => $gallery,
            'subject_label' => $gallery->title,
            'description' => 'Gallery updated',
            'old_values' => $oldValues,
            'new_values' => $gallery->toArray(),
            'risk_level' => 'low',
        ]);

        return redirect()->route('cms.galleries.index')
            ->with('success', db_trans('gallery_updated_successfully'));
    }

    public function destroy(Request $request, Gallery $gallery): RedirectResponse
    {
        abort_unless($request->user()?->can('cms.galleries.delete'), 403);

        $oldValues = $gallery->toArray();
        $title = $gallery->title;

        $this->galleryContentService->deleteGallery($gallery);

        $this->auditLogService->log([
            'user' => $request->user(),
            'event' => 'deleted',
            'module' => 'cms',
            'action' => 'Gallery deleted',
            'subject_type' => Gallery::class,
            'subject_id' => $oldValues['id'] ?? null,
            'subject_label' => $title,
            'description' => 'Gallery deleted',
            'old_values' => $oldValues,
            'risk_level' => 'medium',
        ]);

        return redirect()->route('cms.galleries.index')
            ->with('success', db_trans('gallery_deleted_successfully'));
    }

    public function storeImage(StoreGalleryImageRequest $request, Gallery $gallery): RedirectResponse
    {
        $data = $request->validated();

        if ($path = $this->mediaUploadService->store($request->file('image_file'), 'cms/galleries')) {
            $data['image_path'] = $path;
        }

        unset($data['image_file']);

        $image = $this->galleryContentService->addImage($gallery, $data);

        $this->auditLogService->log([
            'user' => $request->user(),
            'event' => 'created',
            'module' => 'cms',
            'action' => 'Gallery image added',
            'subject' => $gallery,
            'subject_label' => $gallery->title,
            'description' => 'Gallery image added',
            'new_values' => $image->toArray(),
            'risk_level' => 'low',
        ]);

        return back()->with('success', db_trans('gallery_image_added_successfully'));
    }

    public function destroyImage(Request $request, Gallery $gallery, GalleryImage $image): RedirectResponse
    {
        abort_unless($request->user()?->can('cms.galleries.update'), 403);

        $oldValues = $image->toArray();

        $this->galleryContentService->deleteImage($image);

        $this->auditLogService->log([
            'user' => $request->user(),
            'event' => 'deleted',
            'module' => 'cms',
            'action' => 'Gallery image deleted',
            'subject' => $gallery,
            'subject_label' => $gallery->title,
            'description' => 'Gallery image deleted',
            'old_values' => $oldValues,
            'risk_level' => 'low',
        ]);

        return back()->with('success', db_trans('gallery_image_deleted_successfully'));
    }

    protected function withUploadedCover(array $data, Request $request): array
    {
        if ($path = $this->mediaUploadService->store($request->file('cover_image_file'), 'cms/galleries')) {
            $data['cover_image'] = $path;
        }

        unset($data['cover_image_file']);

        return $data;
    }
}
