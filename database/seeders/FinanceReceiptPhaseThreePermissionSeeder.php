<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class FinanceReceiptPhaseThreePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'finance.receipts.verify',
            'finance.receipts.delivery',
            'finance.receipts.settings',
            'finance.receipts.access.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }
    }
}
