<?php

namespace App\Http\Controllers\Admin\Access;

use App\Http\Controllers\Controller;
use App\Http\Requests\Access\StoreRoleRequest;
use App\Http\Requests\Access\UpdateRoleRequest;
use App\Models\Permission;
use App\Services\Access\RoleManagementService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct(
        protected RoleManagementService $roleManagementService
    ) {
    }

    public function index(Request $request): View
    {
        abort_unless($request->user()?->can('access.roles.view'), 403);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:1000'],
        ]);

        return view('admin.access.roles.index', [
            'roles' => $this->roleManagementService->paginate($filters, (int) ($filters['per_page'] ?? 1000)),
            'filters' => $filters,
        ]);
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()?->can('access.roles.create'), 403);

        return view('admin.access.roles.create', [
            'permissions' => Permission::query()->active()->orderBy('module')->orderBy('name')->get(),
        ]);
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $role = $this->roleManagementService->create($request->validated(), $request->user());

        return redirect()
            ->route('system-access.roles.edit', $role->id)
            ->with('success', db_trans('role_created_successfully'));
    }

    public function edit(Role $role, Request $request): View
    {
        abort_unless($request->user()?->can('access.roles.update'), 403);

        $role->load('permissions');

        return view('admin.access.roles.edit', [
            'role' => $role,
            'permissions' => Permission::query()->active()->orderBy('module')->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $this->roleManagementService->update($role, $request->validated(), $request->user());

        return redirect()
            ->route('system-access.roles.edit', $role->id)
            ->with('success', db_trans('role_updated_successfully'));
    }

    public function destroy(Role $role, Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('access.roles.delete'), 403);

        $this->roleManagementService->delete($role, $request->user());

        return redirect()
            ->route('system-access.roles.index')
            ->with('success', db_trans('role_deleted_successfully'));
    }
}