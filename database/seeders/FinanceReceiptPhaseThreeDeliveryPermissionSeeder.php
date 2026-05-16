<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class FinanceReceiptPhaseThreeDeliveryPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'finance.receipts.delivery',
            'finance.receipts.send-sms',
            'finance.receipts.resend-sms',
            'finance.receipts.settings',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }
    }
}
