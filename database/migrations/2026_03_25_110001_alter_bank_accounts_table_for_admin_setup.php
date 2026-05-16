<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            if (! Schema::hasColumn('bank_accounts', 'status')) {
                $table->string('status')->default('active')->after('branch_name');
            }

            if (! Schema::hasColumn('bank_accounts', 'description')) {
                $table->text('description')->nullable()->after('status');
            }

            if (! Schema::hasColumn('bank_accounts', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            foreach (['status', 'description', 'is_active'] as $column) {
                if (Schema::hasColumn('bank_accounts', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
