<?php

namespace App\Http\Controllers\Admin\Audit;

use App\Http\Controllers\Controller;
use App\Http\Requests\Audit\ActivityLogFilterRequest;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Contracts\View\View;

class ActivityLogController extends Controller
{
    public function index(ActivityLogFilterRequest $request): View
    {
        abort_unless($request->user()?->can('audit.logs.view'), 403);

        $filters = $request->validated();
        $perPage = (int) ($filters['per_page'] ?? 20);

        $query = AuditLog::query()
            ->with('user')
            ->latest()
            ->search($filters['search'] ?? null)
            ->forModule($filters['module'] ?? null)
            ->forEvent($filters['event'] ?? null)
            ->forActor(isset($filters['user_id']) ? (int) $filters['user_id'] : null)
            ->forRisk($filters['risk_level'] ?? null);

        if (! empty($filters['from'])) {
            $query->whereDate('created_at', '>=', $filters['from']);
        }

        if (! empty($filters['to'])) {
            $query->whereDate('created_at', '<=', $filters['to']);
        }

        $logs = $query->paginate($perPage)->withQueryString();

        $modules = AuditLog::query()
            ->whereNotNull('module')
            ->distinct()
            ->orderBy('module')
            ->pluck('module');

        $events = AuditLog::query()
            ->whereNotNull('event')
            ->distinct()
            ->orderBy('event')
            ->pluck('event');

        $users = User::query()->orderBy('name')->get(['id', 'name']);

        return view('admin.audit.activity.index', [
            'logs' => $logs,
            'filters' => $filters,
            'modules' => $modules,
            'events' => $events,
            'users' => $users,
        ]);
    }
}