<?php

namespace App\Http\Controllers\Admin\Access;

use App\Http\Controllers\Controller;
use App\Http\Requests\Access\StoreAdminUserRequest;
use App\Http\Requests\Access\UpdateAdminUserRequest;
use App\Models\AuditLog;
use App\Models\Jumuiya;
use App\Models\Kanda;
use App\Models\LoginHistory;
use App\Models\User;
use App\Services\Access\AdminUserService;
use App\Support\AdminScope;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class AdminUserController extends Controller
{
    public function __construct(
        protected AdminUserService $adminUserService
    ) {
    }

    public function index(Request $request): View
    {
        abort_unless($request->user()?->can('access.users.view'), 403);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'role_id' => ['nullable', 'integer', 'exists:roles,id'],
            'is_active' => ['nullable'],
            'kanda_id' => ['nullable', 'integer', 'exists:kandas,id'],
            'jumuiya_id' => ['nullable', 'integer', 'exists:jumuiyas,id'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:1000'],
        ]);

        $users = $this->adminUserService->paginate($filters, (int) ($filters['per_page'] ?? 1000));

        return view('admin.access.users.index', [
            'users' => $users,
            'filters' => $filters,
            'roles' => Role::query()->orderBy('name')->get(['id', 'name']),
            'kandas' => Kanda::query()->orderBy('name')->get(['id', 'name']),
            'jumuiyas' => Jumuiya::query()->orderBy('name')->get(['id', 'name', 'kanda_id']),
        ]);
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()?->can('access.users.create'), 403);

        return view('admin.access.users.create', [
            'kandas' => Kanda::query()->orderBy('name')->get(['id', 'name']),
            'roles' => Role::query()->orderBy('name')->get(['id', 'name']),
            'scopeTypes' => $this->scopeTypes(),
            'selectedScopeType' => AdminScope::TYPE_GLOBAL,
        ]);
    }

    public function store(StoreAdminUserRequest $request): RedirectResponse
    {
        $result = $this->adminUserService->create($request->validated(), $request->user());

        return redirect()
            ->route('system-access.users.show', $result['user']->id)
            ->with('success', db_trans('admin_user_created_successfully'));
    }

    public function show(User $user, Request $request): View
    {
        abort_unless($request->user()?->can('access.users.profile.view'), 403);

        $user->load(['member', 'kanda', 'jumuiya', 'roles']);

        $activityLogs = AuditLog::query()
            ->with('user')
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(10, ['*'], 'activity_page');

        $loginHistories = LoginHistory::query()
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(10, ['*'], 'login_page');

        return view('admin.access.users.show', [
            'adminUser' => $user,
            'activityLogs' => $activityLogs,
            'loginHistories' => $loginHistories,
        ]);
    }

    public function edit(User $user, Request $request): View
    {
        abort_unless($request->user()?->can('access.users.update'), 403);

        $user->load(['member', 'roles', 'kanda', 'jumuiya']);

        return view('admin.access.users.edit', [
            'adminUser' => $user,
            'roles' => Role::query()->orderBy('name')->get(['id', 'name']),
            'kandas' => Kanda::query()->orderBy('name')->get(['id', 'name']),
            'scopeTypes' => $this->scopeTypes(),
            'selectedScopeType' => $this->detectScopeType($user),
        ]);
    }

    public function update(UpdateAdminUserRequest $request, User $user): RedirectResponse
    {
        $this->adminUserService->update($user, $request->validated(), $request->user());

        return redirect()
            ->route('system-access.users.show', $user->id)
            ->with('success', db_trans('admin_user_updated_successfully'));
    }

    public function activate(User $user, Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('access.users.activate'), 403);

        $this->adminUserService->activate($user, $request->user());

        return back()->with('success', db_trans('admin_user_activated_successfully'));
    }

    public function deactivate(User $user, Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('access.users.deactivate'), 403);

        $this->adminUserService->deactivate($user, $request->user());

        return back()->with('success', db_trans('admin_user_deactivated_successfully'));
    }

    public function destroy(User $user, Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('access.users.delete'), 403);

        $this->adminUserService->delete($user, $request->user());

        return redirect()
            ->route('system-access.users.index')
            ->with('success', db_trans('admin_user_deleted_successfully'));
    }

    public function members(Request $request): JsonResponse
    {
        abort_unless(
            $request->user()?->can('access.users.create') || $request->user()?->can('access.users.update'),
            403
        );

        $validated = $request->validate([
            'scope_type' => ['nullable', 'string'],
            'kanda_id' => ['nullable', 'integer', 'exists:kandas,id'],
            'jumuiya_id' => ['nullable', 'integer', 'exists:jumuiyas,id'],
            'search' => ['nullable', 'string', 'max:255'],
        ]);

        $scopeType = $validated['scope_type'] ?? AdminScope::TYPE_GLOBAL;

        $kandaId = null;
        $jumuiyaId = null;

        if ($scopeType === AdminScope::TYPE_KANDA) {
            $kandaId = $validated['kanda_id'] ?? null;
        }

        if ($scopeType === AdminScope::TYPE_JUMUIYA) {
            $kandaId = $validated['kanda_id'] ?? null;
            $jumuiyaId = $validated['jumuiya_id'] ?? null;
        }

        $members = $this->adminUserService->getEligibleMembers(
            $kandaId,
            $jumuiyaId,
            $validated['search'] ?? null
        );

        return response()->json(
            $members->map(function ($member) {
                return [
                    'id' => $member->id,
                    'name' => trim(collect([
                        $member->first_name,
                        $member->middle_name,
                        $member->last_name,
                    ])->filter()->implode(' ')),
                    'member_code' => $member->member_code,
                    'phone' => $member->phone,
                    'jumuiya_id' => $member->familia?->jumuiya_id,
                    'jumuiya_name' => $member->familia?->jumuiya?->name,
                    'kanda_id' => $member->familia?->jumuiya?->kanda_id,
                    'kanda_name' => $member->familia?->jumuiya?->kanda?->name,
                ];
            })->values()
        );
    }

    public function jumuiyas(Request $request): JsonResponse
    {
        abort_unless(
            $request->user()?->can('access.users.create') || $request->user()?->can('access.users.update'),
            403
        );

        $validated = $request->validate([
            'kanda_id' => ['nullable', 'integer', 'exists:kandas,id'],
        ]);

        if (empty($validated['kanda_id'])) {
            return response()->json(collect());
        }

        $jumuiyas = Jumuiya::query()
            ->where('kanda_id', (int) $validated['kanda_id'])
            ->orderBy('name')
            ->get(['id', 'name', 'kanda_id']);

        return response()->json($jumuiyas);
    }

    protected function scopeTypes(): array
    {
        return [
            AdminScope::TYPE_GLOBAL => db_trans('global_scope'),
            AdminScope::TYPE_KANDA => db_trans('kanda_scope'),
            AdminScope::TYPE_JUMUIYA => db_trans('jumuiya_scope'),
        ];
    }

    protected function detectScopeType(User $user): string
    {
        if ($user->jumuiya_id) {
            return AdminScope::TYPE_JUMUIYA;
        }

        if ($user->kanda_id) {
            return AdminScope::TYPE_KANDA;
        }

        return AdminScope::TYPE_GLOBAL;
    }
}