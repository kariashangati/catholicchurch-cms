<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cms\UpdateFooterCenterDetailsRequest;
use App\Services\Audit\AuditLogService;
use App\Services\Cms\FooterCenterDetailsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FooterCenterDetailsController extends Controller
{
    public function __construct(
        protected FooterCenterDetailsService $footerCenterDetailsService,
        protected AuditLogService $auditLogService
    ) {
    }

    public function edit(Request $request): View
    {
        abort_unless($request->user()?->can('cms.footer.view'), 403);

        return view('admin.cms.footer.edit', [
            'settings' => $this->footerCenterDetailsService->data(),
        ]);
    }

    public function update(UpdateFooterCenterDetailsRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $this->footerCenterDetailsService->update($data);

        $this->auditLogService->log([
            'user' => $request->user(),
            'event' => 'updated',
            'module' => 'cms',
            'action' => 'Footer and center details updated',
            'subject_type' => 'cms_footer',
            'subject_id' => null,
            'subject_label' => 'Footer & Center Details',
            'description' => 'Footer content and center details updated',
            'new_values' => $data,
            'risk_level' => 'low',
        ]);

        return back()->with('success', db_trans('footer_center_details_updated_successfully'));
    }
}