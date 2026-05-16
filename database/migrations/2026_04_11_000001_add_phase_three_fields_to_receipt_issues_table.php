<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('receipt_issues', function (Blueprint $table): void {
            if (!Schema::hasColumn('receipt_issues', 'access_token')) {
                $table->string('access_token')->nullable()->unique()->after('receipt_no');
            }

            if (!Schema::hasColumn('receipt_issues', 'access_expires_at')) {
                $table->timestamp('access_expires_at')->nullable()->after('access_token');
            }

            if (!Schema::hasColumn('receipt_issues', 'sms_sent_at')) {
                $table->timestamp('sms_sent_at')->nullable()->after('access_expires_at');
            }

            if (!Schema::hasColumn('receipt_issues', 'sms_sent_to')) {
                $table->string('sms_sent_to', 30)->nullable()->after('sms_sent_at');
            }

            if (!Schema::hasColumn('receipt_issues', 'first_opened_at')) {
                $table->timestamp('first_opened_at')->nullable()->after('sms_sent_to');
            }

            if (!Schema::hasColumn('receipt_issues', 'last_opened_at')) {
                $table->timestamp('last_opened_at')->nullable()->after('first_opened_at');
            }

            if (!Schema::hasColumn('receipt_issues', 'first_downloaded_at')) {
                $table->timestamp('first_downloaded_at')->nullable()->after('last_opened_at');
            }

            if (!Schema::hasColumn('receipt_issues', 'last_downloaded_at')) {
                $table->timestamp('last_downloaded_at')->nullable()->after('first_downloaded_at');
            }

            if (!Schema::hasColumn('receipt_issues', 'download_count')) {
                $table->unsignedInteger('download_count')->default(0)->after('last_downloaded_at');
            }

            if (!Schema::hasColumn('receipt_issues', 'print_count')) {
                $table->unsignedInteger('print_count')->default(0)->after('download_count');
            }

            if (!Schema::hasColumn('receipt_issues', 'last_reprinted_at')) {
                $table->timestamp('last_reprinted_at')->nullable()->after('print_count');
            }

            if (!Schema::hasColumn('receipt_issues', 'delivery_status')) {
                $table->string('delivery_status', 30)->default('pending')->after('last_reprinted_at');
            }

            if (!Schema::hasColumn('receipt_issues', 'delivery_channel')) {
                $table->string('delivery_channel', 30)->nullable()->after('delivery_status');
            }

            if (!Schema::hasColumn('receipt_issues', 'sms_last_resent_at')) {
                $table->timestamp('sms_last_resent_at')->nullable()->after('delivery_channel');
            }
        });
    }

    public function down(): void
    {
        Schema::table('receipt_issues', function (Blueprint $table): void {
            $columns = [
                'access_token',
                'access_expires_at',
                'sms_sent_at',
                'sms_sent_to',
                'first_opened_at',
                'last_opened_at',
                'first_downloaded_at',
                'last_downloaded_at',
                'download_count',
                'print_count',
                'last_reprinted_at',
                'delivery_status',
                'delivery_channel',
                'sms_last_resent_at',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('receipt_issues', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
