<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('communication_campaigns', function (Blueprint $table): void {
            if (! Schema::hasColumn('communication_campaigns', 'requires_approval')) {
                $table->boolean('requires_approval')->default(false)->after('status');
            }
            if (! Schema::hasColumn('communication_campaigns', 'approval_status')) {
                $table->string('approval_status', 30)->default('not_required')->after('requires_approval');
            }
            if (! Schema::hasColumn('communication_campaigns', 'submitted_for_approval_at')) {
                $table->timestamp('submitted_for_approval_at')->nullable()->after('approval_status');
            }
            if (! Schema::hasColumn('communication_campaigns', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('submitted_for_approval_at');
            }
            if (! Schema::hasColumn('communication_campaigns', 'approval_notes')) {
                $table->text('approval_notes')->nullable()->after('approved_at');
            }
            if (! Schema::hasColumn('communication_campaigns', 'quiet_hours_enabled')) {
                $table->boolean('quiet_hours_enabled')->default(false)->after('approval_notes');
            }
            if (! Schema::hasColumn('communication_campaigns', 'quiet_hours_start')) {
                $table->time('quiet_hours_start')->nullable()->after('quiet_hours_enabled');
            }
            if (! Schema::hasColumn('communication_campaigns', 'quiet_hours_end')) {
                $table->time('quiet_hours_end')->nullable()->after('quiet_hours_start');
            }
            if (! Schema::hasColumn('communication_campaigns', 'duplicate_window_hours')) {
                $table->unsignedSmallInteger('duplicate_window_hours')->default(24)->after('quiet_hours_end');
            }
            if (! Schema::hasColumn('communication_campaigns', 'allow_retry_failed')) {
                $table->boolean('allow_retry_failed')->default(true)->after('duplicate_window_hours');
            }
            if (! Schema::hasColumn('communication_campaigns', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('allow_retry_failed');
            }
            if (! Schema::hasColumn('communication_campaigns', 'cancellation_reason')) {
                $table->text('cancellation_reason')->nullable()->after('cancelled_at');
            }
            if (! Schema::hasColumn('communication_campaigns', 'launched_at')) {
                $table->timestamp('launched_at')->nullable()->after('cancellation_reason');
            }

            $table->index(['approval_status', 'status'], 'cc_campaign_approval_status_idx');
            $table->index(['scheduled_at', 'status'], 'cc_campaign_scheduled_status_idx');
        });
    }

    public function down(): void
    {
        Schema::table('communication_campaigns', function (Blueprint $table): void {
            $table->dropIndex('cc_campaign_approval_status_idx');
            $table->dropIndex('cc_campaign_scheduled_status_idx');

            $columns = [
                'requires_approval',
                'approval_status',
                'submitted_for_approval_at',
                'approved_at',
                'approval_notes',
                'quiet_hours_enabled',
                'quiet_hours_start',
                'quiet_hours_end',
                'duplicate_window_hours',
                'allow_retry_failed',
                'cancelled_at',
                'cancellation_reason',
                'launched_at',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('communication_campaigns', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
