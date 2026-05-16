<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('communication_campaigns', function (Blueprint $table) {
            if (! Schema::hasColumn('communication_campaigns', 'scheduled_at')) {
                $table->timestamp('scheduled_at')->nullable()->index()->after('status');
            }

            if (! Schema::hasColumn('communication_campaigns', 'schedule_timezone')) {
                $table->string('schedule_timezone', 64)->nullable()->after('scheduled_at');
            }

            if (! Schema::hasColumn('communication_campaigns', 'scheduled_by')) {
                $table->foreignId('scheduled_by')->nullable()->after('schedule_timezone')
                    ->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('communication_campaigns', 'schedule_notes')) {
                $table->text('schedule_notes')->nullable()->after('scheduled_by');
            }

            if (! Schema::hasColumn('communication_campaigns', 'launched_at')) {
                $table->timestamp('launched_at')->nullable()->after('schedule_notes');
            }

            if (! Schema::hasColumn('communication_campaigns', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('launched_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('communication_campaigns', function (Blueprint $table) {
            foreach (['scheduled_by'] as $foreignColumn) {
                if (Schema::hasColumn('communication_campaigns', $foreignColumn)) {
                    try {
                        $table->dropConstrainedForeignId($foreignColumn);
                    } catch (\Throwable $e) {
                        // ignore if FK name differs
                    }
                }
            }

            foreach (['schedule_timezone', 'schedule_notes', 'launched_at', 'cancelled_at'] as $column) {
                if (Schema::hasColumn('communication_campaigns', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
