<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cms\StorePageRequest;
use App\Http\Requests\Cms\UpdatePageRequest;
use App\Models\Page;
use App\Services\Audit\AuditLogService;
use App\Services\Cms\PageContentService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function __construct(
        protected PageContentService $pageContentService,
        protected AuditLogService $auditLogService
    ) {
    }

    public function index(Request $request): View
    {
        abort_unless($request->user()?->can('cms.pages.view'), 403);

        return view('admin.cms.pages.index', [
            'pages' => $this->pageContentService->paginated(),
        ]);
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()?->can('cms.pages.create'), 403);

        return view('admin.cms.pages.create');
    }

    public function store(StorePageRequest $request): RedirectResponse
    {
        $page = $this->pageContentService->create(
            $request->validated(),
            $request->user()?->id
        );

        $this->auditLogService->log([
            'user' => $request->user(),
            'event' => 'created',
            'module' => 'cms',
            'action' => 'Page created',
            'subject' => $page,
            'subject_label' => $page->title,
            'description' => 'Page created',
            'new_values' => $page->toArray(),
            'risk_level' => 'low',
        ]);

        return redirect()->route('cms.pages.index')
            ->with('success', db_trans('page_created_successfully'));
    }

    public function edit(Request $request, Page $page): View
    {
        abort_unless($request->user()?->can('cms.pages.update'), 403);

        return view('admin.cms.pages.edit', compact('page'));
    }

    public function update(UpdatePageRequest $request, Page $page): RedirectResponse
    {
        $oldValues = $page->toArray();
        $page = $this->pageContentService->update(
            $page,
            $request->validated(),
            $request->user()?->id
        );

        $this->auditLogService->log([
            'user' => $request->user(),
            'event' => 'updated',
            'module' => 'cms',
            'action' => 'Page updated',
            'subject' => $page,
            'subject_label' => $page->title,
            'description' => 'Page updated',
            'old_values' => $oldValues,
            'new_values' => $page->toArray(),
            'risk_level' => 'low',
        ]);

        return redirect()->route('cms.pages.index')
            ->with('success', db_trans('page_updated_successfully'));
    }

    public function destroy(Request $request, Page $page): RedirectResponse
    {
        abort_unless($request->user()?->can('cms.pages.delete'), 403);

        $oldValues = $page->toArray();
        $title = $page->title;

        $this->pageContentService->delete($page);

        $this->auditLogService->log([
            'user' => $request->user(),
            'event' => 'deleted',
            'module' => 'cms',
            'action' => 'Page deleted',
            'subject_type' => Page::class,
            'subject_id' => $oldValues['id'] ?? null,
            'subject_label' => $title,
            'description' => 'Page deleted',
            'old_values' => $oldValues,
            'risk_level' => 'medium',
        ]);

        return redirect()->route('cms.pages.index')
            ->with('success', db_trans('page_deleted_successfully'));
    }
}