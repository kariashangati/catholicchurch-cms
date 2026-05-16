<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('receipt_issues', function (Blueprint $table) {
            if (! Schema::hasColumn('receipt_issues', 'sms_delivery_status')) {
                $table->string('sms_delivery_status', 30)->nullable()->after('status');
            }
            if (! Schema::hasColumn('receipt_issues', 'sms_sent_at')) {
                $table->timestamp('sms_sent_at')->nullable()->after('sms_delivery_status');
            }
            if (! Schema::hasColumn('receipt_issues', 'sms_sent_by')) {
                $table->foreignId('sms_sent_by')->nullable()->after('sms_sent_at')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('receipt_issues', 'sms_template_key')) {
                $table->string('sms_template_key')->nullable()->after('sms_sent_by');
            }
            if (! Schema::hasColumn('receipt_issues', 'last_delivery_channel')) {
                $table->string('last_delivery_channel', 30)->nullable()->after('sms_template_key');
            }
            if (! Schema::hasColumn('receipt_issues', 'delivery_attempts')) {
                $table->unsignedInteger('delivery_attempts')->default(0)->after('last_delivery_channel');
            }
        });
    }

    public function down(): void
    {
        Schema::table('receipt_issues', function (Blueprint $table) {
            foreach (['sms_sent_by'] as $fk) {
                # Laravel will infer constraint names; drop column directly in tolerant environments.
            }

            $drops = [
                'sms_delivery_status',
                'sms_sent_at',
                'sms_sent_by',
                'sms_template_key',
                'last_delivery_channel',
                'delivery_attempts',
            ];

            foreach ($drops as $column) {
                if (Schema::hasColumn('receipt_issues', $column)) {
                    try {
                        $table->dropConstrainedForeignId($column);
                    } catch (\Throwable $e) {
                        try {
                            $table->dropColumn($column);
                        } catch (\Throwable $e2) {
                            // no-op
                        }
                    }
                }
            }
        });
    }
};
