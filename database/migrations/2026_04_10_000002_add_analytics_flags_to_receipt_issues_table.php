<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('receipt_issues', function (Blueprint $table): void {
            $table->timestamp('first_accessed_at')->nullable()->after('last_accessed_at');
            $table->unsignedInteger('download_count')->default(0)->after('first_accessed_at');
            $table->unsignedInteger('verification_count')->default(0)->after('download_count');
            $table->unsignedInteger('delivery_attempt_count')->default(0)->after('verification_count');
            $table->string('last_delivery_status', 30)->nullable()->after('delivery_attempt_count');
            $table->string('last_exception_type', 100)->nullable()->after('last_delivery_status');

            $table->index(['status', 'issued_at']);
            $table->index(['last_delivery_status']);
            $table->index(['last_exception_type']);
        });
    }

    public function down(): void
    {
        Schema::table('receipt_issues', function (Blueprint $table): void {
            $table->dropIndex(['status', 'issued_at']);
            $table->dropIndex(['last_delivery_status']);
            $table->dropIndex(['last_exception_type']);
            $table->dropColumn([
                'first_accessed_at',
                'download_count',
                'verification_count',
                'delivery_attempt_count',
                'last_delivery_status',
                'last_exception_type',
            ]);
        });
    }
};
