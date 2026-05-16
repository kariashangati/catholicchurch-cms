<?php

namespace App\Services\Access;

use App\Models\Permission;
use App\Models\User;
use App\Services\Audit\AuditLogService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class RoleManagementService
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Role::query()
            ->withCount(['permissions', 'users'])
            ->orderBy('name');

        if (! empty($filters['search'])) {
            $term = trim((string) $filters['search']);
            $query->where('name', 'like', "%{$term}%");
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function create(array $data, User $actor): Role
    {
        return DB::transaction(function () use ($data, $actor) {
            $role = Role::query()->create([
                'name' => $data['name'],
                'guard_name' => $data['guard_name'],
            ]);

            $permissionIds = collect($data['permission_ids'] ?? [])->map(fn ($id) => (int) $id)->all();
            $permissions = Permission::query()->whereIn('id', $permissionIds)->active()->pluck('name')->all();

            $role->syncPermissions($permissions);

            $this->auditLogService->log([
                'user' => $actor,
                'event' => 'created',
                'module' => 'access',
                'action' => 'Role created',
                'subject_type' => Role::class,
                'subject_id' => $role->id,
                'subject_label' => $role->name,
                'description' => 'Access role created',
                'new_values' => [
                    'name' => $role->name,
                    'permissions' => $permissions,
                ],
                'risk_level' => 'high',
            ]);

            return $role->loadCount(['permissions', 'users']);
        });
    }

    public function update(Role $role, array $data, User $actor): Role
    {
        return DB::transaction(function () use ($role, $data, $actor) {
            $oldValues = [
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name')->all(),
            ];

            $role->update([
                'name' => $data['name'],
                'guard_name' => $data['guard_name'],
            ]);

            $permissionIds = collect($data['permission_ids'] ?? [])->map(fn ($id) => (int) $id)->all();
            $permissions = Permission::query()->whereIn('id', $permissionIds)->active()->pluck('name')->all();

            $role->syncPermissions($permissions);

            $this->auditLogService->log([
                'user' => $actor,
                'event' => 'updated',
                'module' => 'access',
                'action' => 'Role updated',
                'subject_type' => Role::class,
                'subject_id' => $role->id,
                'subject_label' => $role->name,
                'description' => 'Access role updated',
                'old_values' => $oldValues,
                'new_values' => [
                    'name' => $role->name,
                    'permissions' => $permissions,
                ],
                'risk_level' => 'high',
            ]);

            return $role->fresh()->loadCount(['permissions', 'users']);
        });
    }

    public function delete(Role $role, User $actor): void
    {
        DB::transaction(function () use ($role, $actor) {
            $name = $role->name;
            $role->syncPermissions([]);
            $role->delete();

            $this->auditLogService->log([
                'user' => $actor,
                'event' => 'deleted',
                'module' => 'access',
                'action' => 'Role deleted',
                'subject_type' => Role::class,
                'subject_id' => null,
                'subject_label' => $name,
                'description' => 'Access role deleted',
                'risk_level' => 'high',
            ]);
        });
    }
}