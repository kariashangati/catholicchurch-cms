<?php

namespace App\Http\Controllers\Admin\Access;

use App\Http\Controllers\Controller;
use App\Http\Requests\Access\StorePermissionRequest;
use App\Http\Requests\Access\UpdatePermissionRequest;
use App\Models\Permission;
use App\Services\Access\PermissionManagementService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function __construct(
        protected PermissionManagementService $permissionManagementService
    ) {
    }

    public function index(Request $request): View
    {
        abort_unless($request->user()?->can('access.permissions.view'), 403);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'module' => ['nullable', 'string', 'max:100'],
            'is_active' => ['nullable'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:1000'],
        ]);

        return view('admin.access.permissions.index', [
            'permissions' => $this->permissionManagementService->paginate($filters, (int) ($filters['per_page'] ?? 1000)),
            'filters' => $filters,
            'modules' => Permission::query()->distinct()->orderBy('module')->pluck('module'),
        ]);
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()?->can('access.permissions.create'), 403);

        return view('admin.access.permissions.create');
    }

    public function store(StorePermissionRequest $request): RedirectResponse
    {
        $permission = $this->permissionManagementService->create($request->validated(), $request->user());

        return redirect()
            ->route('system-access.permissions.edit', $permission->id)
            ->with('success', db_trans('permission_created_successfully'));
    }

    public function edit(Permission $permission, Request $request): View
    {
        abort_unless($request->user()?->can('access.permissions.update'), 403);

        return view('admin.access.permissions.edit', [
            'permission' => $permission,
        ]);
    }

    public function update(UpdatePermissionRequest $request, Permission $permission): RedirectResponse
    {
        $this->permissionManagementService->update($permission, $request->validated(), $request->user());

        return redirect()
            ->route('system-access.permissions.edit', $permission->id)
            ->with('success', db_trans('permission_updated_successfully'));
    }

    public function toggle(Permission $permission, Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('access.permissions.toggle'), 403);

        $this->permissionManagementService->toggle($permission, $request->user());

        return back()->with('success', db_trans('permission_status_updated_successfully'));
    }

    public function destroy(Permission $permission, Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('access.permissions.delete'), 403);

        $this->permissionManagementService->delete($permission, $request->user());

        return redirect()
            ->route('system-access.permissions.index')
            ->with('success', db_trans('permission_deleted_successfully'));
    }
}