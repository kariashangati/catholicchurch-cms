<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class CommunicationCenterPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'communication.view',
            'communication.send_single',
            'communication.send_bulk',
            'communication.schedule',
            'communication.manage_templates',
            'communication.manage_automations',
            'communication.view_logs',
            'communication.manage_preferences',
            'communication.manage_balance',
            'communication.approve_campaigns',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }
    }
}
