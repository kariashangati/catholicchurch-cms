<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
class FinanceReceiptBundleCPermissionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['finance.receipts.analytics','finance.receipts.exceptions','finance.receipts.exceptions.resolve'] as $permission) {
            Permission::findOrCreate($permission, 'web');
        }
    }
}
