<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tithes', function (Blueprint $table) {
            if (! Schema::hasColumn('tithes', 'tithe_batch_id')) {
                $table->unsignedBigInteger('tithe_batch_id')->nullable()->after('jumuiya_id');
                $table->unsignedSmallInteger('tithe_year')->nullable()->after('contribution_date');
                $table->unsignedTinyInteger('tithe_month')->nullable()->after('tithe_year');
                $table->text('override_reason')->nullable()->after('notes');
                $table->unsignedBigInteger('override_approved_by')->nullable()->after('override_reason');
                $table->timestamp('override_approved_at')->nullable()->after('override_approved_by');
                $table->index(['member_id', 'tithe_year', 'tithe_month'], 'tithes_member_period_idx');
                $table->unique(['member_id', 'contribution_date', 'amount'], 'tithes_exact_duplicate_block');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tithes', function (Blueprint $table) {
            foreach (['tithes_member_period_idx', 'tithes_exact_duplicate_block'] as $index) {
                try { $table->dropIndex($index); } catch (Throwable $e) {}
            }
            foreach (['tithe_batch_id', 'tithe_year', 'tithe_month', 'override_reason', 'override_approved_by', 'override_approved_at'] as $column) {
                if (Schema::hasColumn('tithes', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
