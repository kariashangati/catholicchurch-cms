<?php

namespace App\Services\Access;

use App\Models\Permission;
use App\Models\User;
use Spatie\Permission\Models\Role;

class AccessDashboardService
{
    public function getDataFor(User $user): array
    {
        abort_unless($user->can('access.dashboard.view'), 403);

        $totalUsers = User::query()->count();
        $activeUsers = User::query()->where('is_active', true)->count();
        $inactiveUsers = User::query()->where('is_active', false)->count();

        $rolesCount = Role::query()->count();
        $permissionsCount = Permission::query()->count();
        $disabledPermissions = Permission::query()->where('is_active', false)->count();

        $recentUsers = User::query()
            ->with(['roles', 'kanda', 'jumuiya', 'member'])
            ->latest()
            ->take(25)
            ->get();

        $recentRoles = Role::query()
            ->withCount(['users', 'permissions'])
            ->orderByDesc('permissions_count')
            ->orderBy('name')
            ->take(12)
            ->get();

        /*
         * MySQL strict mode / ONLY_FULL_GROUP_BY safe:
         * We group by the real column only, then normalize NULL/empty module names in PHP.
         */
        $permissionModules = Permission::query()
            ->selectRaw('permissions.module as module_name, COUNT(*) as total')
            ->groupBy('permissions.module')
            ->orderBy('permissions.module')
            ->get()
            ->groupBy(function ($row) {
                return blank($row->module_name) ? 'general' : (string) $row->module_name;
            })
            ->map(function ($rows, string $module) {
                return (object) [
                    'permission_module' => $module,
                    'total' => $rows->sum(fn ($row) => (int) $row->total),
                ];
            })
            ->sortBy('permission_module')
            ->values();

        return [
            'page' => [
                'title' => db_trans('access_control_center'),
                'updated_at' => now(),
            ],

            'kpis' => [
                [
                    'title' => db_trans('total_admin_users'),
                    'value' => $totalUsers,
                    'icon' => 'fas fa-user-shield',
                    'tone' => 'primary',
                    'url' => route('system-access.users.index'),
                ],
                [
                    'title' => db_trans('active_admin_users'),
                    'value' => $activeUsers,
                    'icon' => 'fas fa-user-check',
                    'tone' => 'success',
                    'url' => route('system-access.users.index', ['is_active' => 1]),
                ],
                [
                    'title' => db_trans('inactive_admin_users'),
                    'value' => $inactiveUsers,
                    'icon' => 'fas fa-user-slash',
                    'tone' => 'warning',
                    'url' => route('system-access.users.index', ['is_active' => 0]),
                ],
                [
                    'title' => db_trans('roles_count'),
                    'value' => $rolesCount,
                    'icon' => 'fas fa-id-badge',
                    'tone' => 'info',
                    'url' => route('system-access.roles.index'),
                ],
                [
                    'title' => db_trans('permissions_count'),
                    'value' => $permissionsCount,
                    'icon' => 'fas fa-key',
                    'tone' => 'secondary',
                    'url' => route('system-access.permissions.index'),
                ],
                [
                    'title' => db_trans('disabled_permissions'),
                    'value' => $disabledPermissions,
                    'icon' => 'fas fa-toggle-off',
                    'tone' => 'danger',
                    'url' => route('system-access.permissions.index', ['is_active' => 0]),
                ],
            ],

            'recentUsers' => $recentUsers,
            'recentRoles' => $recentRoles,
            'permissionModules' => $permissionModules,

            'roleChart' => [
                'labels' => $recentRoles
                    ->pluck('name')
                    ->values(),
                'permissions' => $recentRoles
                    ->pluck('permissions_count')
                    ->map(fn ($value) => (int) $value)
                    ->values(),
                'users' => $recentRoles
                    ->pluck('users_count')
                    ->map(fn ($value) => (int) $value)
                    ->values(),
            ],

            'permissionModuleChart' => [
                'labels' => $permissionModules
                    ->pluck('permission_module')
                    ->map(fn ($module) => ucfirst((string) $module))
                    ->values(),
                'values' => $permissionModules
                    ->pluck('total')
                    ->map(fn ($value) => (int) $value)
                    ->values(),
            ],
        ];
    }
}