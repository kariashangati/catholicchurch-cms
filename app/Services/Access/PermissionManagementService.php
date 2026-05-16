<?php

namespace App\Services\Access;

use App\Models\Permission;
use App\Models\User;
use App\Services\Audit\AuditLogService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PermissionManagementService
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Permission::query()
            ->withCount('roles')
            ->orderBy('module')
            ->orderBy('name');

        if (! empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (! empty($filters['module'])) {
            $query->forModule($filters['module']);
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function create(array $data, User $actor): Permission
    {
        $permission = Permission::query()->create([
            'name' => $data['name'],
            'guard_name' => $data['guard_name'],
            'module' => $data['module'] ?: $this->inferModule($data['name']),
            'description' => $data['description'] ?? null,
            'is_active' => (bool) $data['is_active'],
        ]);

        $this->auditLogService->log([
            'user' => $actor,
            'event' => 'created',
            'module' => 'access',
            'action' => 'Permission created',
            'subject' => $permission,
            'subject_label' => $permission->name,
            'description' => 'Access permission created',
            'new_values' => $permission->toArray(),
            'risk_level' => 'critical',
        ]);

        return $permission;
    }

    public function update(Permission $permission, array $data, User $actor): Permission
    {
        $oldValues = $permission->toArray();

        $permission->update([
            'name' => $data['name'],
            'guard_name' => $data['guard_name'],
            'module' => $data['module'] ?: $this->inferModule($data['name']),
            'description' => $data['description'] ?? null,
            'is_active' => (bool) $data['is_active'],
        ]);

        $this->auditLogService->log([
            'user' => $actor,
            'event' => 'updated',
            'module' => 'access',
            'action' => 'Permission updated',
            'subject' => $permission,
            'subject_label' => $permission->name,
            'description' => 'Access permission updated',
            'old_values' => $oldValues,
            'new_values' => $permission->fresh()->toArray(),
            'risk_level' => 'critical',
        ]);

        return $permission->fresh();
    }

    public function toggle(Permission $permission, User $actor): Permission
    {
        $permission->update([
            'is_active' => ! $permission->is_active,
        ]);

        $this->auditLogService->log([
            'user' => $actor,
            'event' => 'updated',
            'module' => 'access',
            'action' => $permission->is_active ? 'Permission enabled' : 'Permission disabled',
            'subject' => $permission,
            'subject_label' => $permission->name,
            'description' => 'Permission activation status changed',
            'new_values' => ['is_active' => $permission->is_active],
            'risk_level' => 'critical',
        ]);

        return $permission->fresh();
    }

    public function delete(Permission $permission, User $actor): void
    {
        DB::transaction(function () use ($permission, $actor) {
            $name = $permission->name;
            $permission->roles()->detach();
            $permission->delete();

            $this->auditLogService->log([
                'user' => $actor,
                'event' => 'deleted',
                'module' => 'access',
                'action' => 'Permission deleted',
                'subject_type' => Permission::class,
                'subject_id' => null,
                'subject_label' => $name,
                'description' => 'Access permission deleted',
                'risk_level' => 'critical',
            ]);
        });
    }

    protected function inferModule(string $permissionName): string
    {
        return str_contains($permissionName, '.')
            ? explode('.', $permissionName)[0]
            : 'general';
    }
}