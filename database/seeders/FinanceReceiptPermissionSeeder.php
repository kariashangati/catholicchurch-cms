<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class FinanceReceiptPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'finance.receipts.view',
            'finance.receipts.create',
            'finance.receipts.print',
            'finance.receipts.reprint',
            'finance.receipts.void',
            'finance.receipts.export',
            'finance.receipts.verify',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }
    }
}
