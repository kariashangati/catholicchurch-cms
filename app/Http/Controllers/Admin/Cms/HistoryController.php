<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cms\StoreHistoryRequest;
use App\Http\Requests\Cms\UpdateHistoryRequest;
use App\Models\History;
use App\Services\Audit\AuditLogService;
use App\Services\Cms\CmsMediaUploadService;
use App\Services\Cms\HistoryContentService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
    public function __construct(
        protected HistoryContentService $historyContentService,
        protected CmsMediaUploadService $mediaUploadService,
        protected AuditLogService $auditLogService
    ) {
    }

    public function index(Request $request): View
    {
        abort_unless($request->user()?->can('cms.histories.view'), 403);

        return view('admin.cms.histories.index', [
            'histories' => $this->historyContentService->paginated(),
        ]);
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()?->can('cms.histories.create'), 403);

        return view('admin.cms.histories.create');
    }

    public function store(StoreHistoryRequest $request): RedirectResponse
    {
        $data = $this->withUploadedMedia($request->validated(), $request);
        $history = $this->historyContentService->create($data);

        $this->auditLogService->log([
            'user' => $request->user(),
            'event' => 'created',
            'module' => 'cms',
            'action' => 'History created',
            'subject' => $history,
            'subject_label' => $history->title,
            'description' => 'History created',
            'new_values' => $history->toArray(),
            'risk_level' => 'low',
        ]);

        return redirect()->route('cms.histories.index')
            ->with('success', db_trans('history_created_successfully'));
    }

    public function edit(Request $request, History $history): View
    {
        abort_unless($request->user()?->can('cms.histories.update'), 403);

        return view('admin.cms.histories.edit', compact('history'));
    }

    public function update(UpdateHistoryRequest $request, History $history): RedirectResponse
    {
        $oldValues = $history->toArray();
        $data = $this->withUploadedMedia($request->validated(), $request);
        $history = $this->historyContentService->update($history, $data);

        $this->auditLogService->log([
            'user' => $request->user(),
            'event' => 'updated',
            'module' => 'cms',
            'action' => 'History updated',
            'subject' => $history,
            'subject_label' => $history->title,
            'description' => 'History updated',
            'old_values' => $oldValues,
            'new_values' => $history->toArray(),
            'risk_level' => 'low',
        ]);

        return redirect()->route('cms.histories.index')
            ->with('success', db_trans('history_updated_successfully'));
    }

    public function destroy(Request $request, History $history): RedirectResponse
    {
        abort_unless($request->user()?->can('cms.histories.delete'), 403);

        $oldValues = $history->toArray();
        $title = $history->title;

        $this->historyContentService->delete($history);

        $this->auditLogService->log([
            'user' => $request->user(),
            'event' => 'deleted',
            'module' => 'cms',
            'action' => 'History deleted',
            'subject_type' => History::class,
            'subject_id' => $oldValues['id'] ?? null,
            'subject_label' => $title,
            'description' => 'History deleted',
            'old_values' => $oldValues,
            'risk_level' => 'medium',
        ]);

        return redirect()->route('cms.histories.index')
            ->with('success', db_trans('history_deleted_successfully'));
    }

    protected function withUploadedMedia(array $data, Request $request): array
    {
        if ($path = $this->mediaUploadService->store($request->file('featured_image_file'), 'cms/histories')) {
            $data['featured_image'] = $path;
        }

        unset($data['featured_image_file']);

        return $data;
    }
}
