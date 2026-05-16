<?php

namespace App\Http\Controllers\Admin\Access;

use App\Http\Controllers\Controller;
use App\Http\Requests\Access\UpdateAccessNotificationTemplateRequest;
use App\Models\AccessNotificationTemplate;
use App\Services\Audit\AuditLogService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccessNotificationTemplateController extends Controller
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {
    }

    public function index(Request $request): View
    {
        abort_unless($request->user()?->can('access.templates.view'), 403);

        return view('admin.access.templates.index', [
            'templates' => AccessNotificationTemplate::query()
                ->orderBy('code')
                ->orderBy('locale')
                ->get(),
        ]);
    }

    public function update(UpdateAccessNotificationTemplateRequest $request): RedirectResponse
    {
        $actor = $request->user();
        $payload = $request->validated()['templates'];

        DB::transaction(function () use ($payload, $actor) {
            foreach ($payload as $templateData) {
                $template = AccessNotificationTemplate::query()->findOrFail((int) $templateData['id']);

                $oldValues = $template->only(['name', 'message', 'is_active']);

                $template->update([
                    'name' => $templateData['name'],
                    'message' => $templateData['message'],
                    'is_active' => (bool) $templateData['is_active'],
                    'updated_by' => $actor->id,
                ]);

                $this->auditLogService->log([
                    'user' => $actor,
                    'event' => 'updated',
                    'module' => 'access',
                    'action' => 'Access notification template updated',
                    'subject' => $template,
                    'subject_label' => $template->name,
                    'description' => 'Access notification template updated',
                    'old_values' => $oldValues,
                    'new_values' => $template->fresh()->only(['name', 'message', 'is_active']),
                    'risk_level' => 'medium',
                ]);
            }
        });

        return back()->with('success', db_trans('access_notification_templates_updated_successfully'));
    }
}