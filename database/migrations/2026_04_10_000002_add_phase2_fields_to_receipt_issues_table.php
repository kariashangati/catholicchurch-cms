<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('receipt_issues', function (Blueprint $table) {
            if (! Schema::hasColumn('receipt_issues', 'last_printed_at')) {
                $table->timestamp('last_printed_at')->nullable()->after('printed_at');
            }
            if (! Schema::hasColumn('receipt_issues', 'last_printed_by')) {
                $table->foreignId('last_printed_by')->nullable()->after('last_printed_at')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('receipt_issues', 'reprint_reason')) {
                $table->text('reprint_reason')->nullable()->after('print_count');
            }
            if (! Schema::hasColumn('receipt_issues', 'void_reason')) {
                $table->text('void_reason')->nullable()->after('reprint_reason');
            }
            if (! Schema::hasColumn('receipt_issues', 'is_void')) {
                $table->boolean('is_void')->default(false)->after('void_reason');
            }
            if (! Schema::hasColumn('receipt_issues', 'void_reference_receipt_id')) {
                $table->foreignId('void_reference_receipt_id')->nullable()->after('is_void')->constrained('receipt_issues')->nullOnDelete();
            }
            if (! Schema::hasColumn('receipt_issues', 'batch_reference')) {
                $table->string('batch_reference', 100)->nullable()->after('void_reference_receipt_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('receipt_issues', function (Blueprint $table) {
            foreach (['last_printed_by', 'void_reference_receipt_id'] as $fk) {
                try { $table->dropConstrainedForeignId($fk); } catch (Throwable $e) {}
            }
            foreach (['last_printed_at', 'reprint_reason', 'void_reason', 'is_void', 'batch_reference'] as $col) {
                if (Schema::hasColumn('receipt_issues', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
