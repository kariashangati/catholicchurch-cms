<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cms\StoreAnnouncementRequest;
use App\Http\Requests\Cms\UpdateAnnouncementRequest;
use App\Models\Announcement;
use App\Services\Audit\AuditLogService;
use App\Services\Cms\AnnouncementContentService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function __construct(
        protected AnnouncementContentService $announcementContentService,
        protected AuditLogService $auditLogService
    ) {
    }

    public function index(Request $request): View
    {
        abort_unless($request->user()?->can('cms.announcements.view'), 403);

        return view('admin.cms.announcements.index', [
            'announcements' => $this->announcementContentService->paginated(),
        ]);
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()?->can('cms.announcements.create'), 403);

        return view('admin.cms.announcements.create');
    }

    public function store(StoreAnnouncementRequest $request): RedirectResponse
    {
        $announcement = $this->announcementContentService->create(
            $request->validated(),
            $request->user()?->id
        );

        $this->auditLogService->log([
            'user' => $request->user(),
            'event' => 'created',
            'module' => 'cms',
            'action' => 'Announcement created',
            'subject' => $announcement,
            'subject_label' => $announcement->title,
            'description' => 'Announcement created',
            'new_values' => $announcement->toArray(),
            'risk_level' => 'low',
        ]);

        return redirect()->route('cms.announcements.index')
            ->with('success', db_trans('announcement_created_successfully'));
    }

    public function edit(Request $request, Announcement $announcement): View
    {
        abort_unless($request->user()?->can('cms.announcements.update'), 403);

        return view('admin.cms.announcements.edit', compact('announcement'));
    }

    public function update(UpdateAnnouncementRequest $request, Announcement $announcement): RedirectResponse
    {
        $oldValues = $announcement->toArray();
        $announcement = $this->announcementContentService->update(
            $announcement,
            $request->validated(),
            $request->user()?->id
        );

        $this->auditLogService->log([
            'user' => $request->user(),
            'event' => 'updated',
            'module' => 'cms',
            'action' => 'Announcement updated',
            'subject' => $announcement,
            'subject_label' => $announcement->title,
            'description' => 'Announcement updated',
            'old_values' => $oldValues,
            'new_values' => $announcement->toArray(),
            'risk_level' => 'low',
        ]);

        return redirect()->route('cms.announcements.index')
            ->with('success', db_trans('announcement_updated_successfully'));
    }

    public function destroy(Request $request, Announcement $announcement): RedirectResponse
    {
        abort_unless($request->user()?->can('cms.announcements.delete'), 403);

        $oldValues = $announcement->toArray();
        $title = $announcement->title;

        $this->announcementContentService->delete($announcement);

        $this->auditLogService->log([
            'user' => $request->user(),
            'event' => 'deleted',
            'module' => 'cms',
            'action' => 'Announcement deleted',
            'subject_type' => Announcement::class,
            'subject_id' => $oldValues['id'] ?? null,
            'subject_label' => $title,
            'description' => 'Announcement deleted',
            'old_values' => $oldValues,
            'risk_level' => 'medium',
        ]);

        return redirect()->route('cms.announcements.index')
            ->with('success', db_trans('announcement_deleted_successfully'));
    }
}