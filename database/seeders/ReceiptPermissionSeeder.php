<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class ReceiptPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'receipts.view',
            'receipts.create',
            'receipts.issue',
            'receipts.preview',
            'receipts.history.view',
            'receipts.pending.view',
            'receipts.detail.view',
            'receipts.download',
            'receipts.reprint',
            'receipts.send_sms',
            'receipts.resend_sms',
            'receipts.exceptions.view',
            'receipts.verification.view',
            'receipts.analytics.view',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }
    }
}
