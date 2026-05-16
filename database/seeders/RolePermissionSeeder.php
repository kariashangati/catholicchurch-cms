<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'dashboard.view',
            'members.view',
            'members.create',
            'members.update',
            'members.delete',
            'reports.view',
            'translations.view',
            'translations.create',
            'translations.update',
            'translations.delete',
            'finance.view',
            'settings.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $parishAdmin = Role::firstOrCreate(['name' => 'Parish Admin', 'guard_name' => 'web']);
        $kandaLeader = Role::firstOrCreate(['name' => 'Kanda Leader', 'guard_name' => 'web']);
        $jumuiyaLeader = Role::firstOrCreate(['name' => 'Jumuiya Leader', 'guard_name' => 'web']);
        $viewer = Role::firstOrCreate(['name' => 'Viewer', 'guard_name' => 'web']);

        $superAdmin->syncPermissions($permissions);

        $parishAdmin->syncPermissions([
            'dashboard.view',
            'members.view',
            'members.create',
            'members.update',
            'reports.view',
            'finance.view',
        ]);

        $kandaLeader->syncPermissions([
            'dashboard.view',
            'members.view',
            'reports.view',
        ]);

        $jumuiyaLeader->syncPermissions([
            'dashboard.view',
            'members.view',
        ]);

        $viewer->syncPermissions([
            'dashboard.view',
        ]);
    }
}