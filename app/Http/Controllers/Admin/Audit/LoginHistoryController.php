<?php

namespace App\Http\Controllers\Admin\Audit;

use App\Http\Controllers\Controller;
use App\Http\Requests\Audit\LoginHistoryFilterRequest;
use App\Models\LoginHistory;
use App\Models\User;
use Illuminate\Contracts\View\View;

class LoginHistoryController extends Controller
{
    public function index(LoginHistoryFilterRequest $request): View
    {
        abort_unless($request->user()?->can('audit.logins.view'), 403);

        $filters = $request->validated();
        $perPage = (int) ($filters['per_page'] ?? 20);

        $query = LoginHistory::query()
            ->with('user')
            ->latest()
            ->search($filters['search'] ?? null)
            ->forUser(isset($filters['user_id']) ? (int) $filters['user_id'] : null)
            ->forStatus($filters['status'] ?? null)
            ->forRisk($filters['risk_level'] ?? null)
            ->suspiciousOnly((bool) ($filters['suspicious_only'] ?? false));

        if (! empty($filters['from'])) {
            $query->whereDate('created_at', '>=', $filters['from']);
        }

        if (! empty($filters['to'])) {
            $query->whereDate('created_at', '<=', $filters['to']);
        }

        $logins = $query->paginate($perPage)->withQueryString();

        $users = User::query()->orderBy('name')->get(['id', 'name']);

        return view('admin.audit.logins.index', [
            'logins' => $logins,
            'filters' => $filters,
            'users' => $users,
        ]);
    }
}