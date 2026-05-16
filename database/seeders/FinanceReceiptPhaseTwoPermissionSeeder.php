<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class FinanceReceiptPhaseTwoPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'finance.receipts.batch',
            'finance.receipts.reprint',
            'finance.receipts.void',
            'finance.receipts.history',
            'finance.receipts.register',
            'finance.receipts.export',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }
    }
}
