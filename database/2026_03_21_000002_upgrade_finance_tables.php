<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('main_offerings', function (Blueprint $table) {
            $table->foreignId('mass_type_id')->nullable()->after('mass_name')->constrained('mass_types')->nullOnDelete();
            $table->string('payment_method', 30)->nullable()->after('mass_type_id');
            $table->string('reference_no', 100)->nullable()->after('payment_method');
            $table->string('receipt_no', 100)->nullable()->after('reference_no');
            $table->foreignId('approved_by')->nullable()->after('recorded_by')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
        });

        Schema::table('cash_contributions', function (Blueprint $table) {
            $table->string('payment_method', 30)->nullable()->after('group_name');
            $table->string('reference_no', 100)->nullable()->after('payment_method');
            $table->string('receipt_no', 100)->nullable()->after('reference_no');
            $table->foreignId('approved_by')->nullable()->after('recorded_by')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
        });

        Schema::table('tithes', function (Blueprint $table) {
            $table->string('payment_method', 30)->nullable()->after('contribution_date');
            $table->string('reference_no', 100)->nullable()->after('payment_method');
            $table->string('receipt_no', 100)->nullable()->after('reference_no');
            $table->foreignId('approved_by')->nullable()->after('recorded_by')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
        });
    }

    public function down(): void
    {
        Schema::table('main_offerings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('mass_type_id');
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn(['payment_method', 'reference_no', 'receipt_no', 'approved_at']);
        });

        Schema::table('cash_contributions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn(['payment_method', 'reference_no', 'receipt_no', 'approved_at']);
        });

        Schema::table('tithes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn(['payment_method', 'reference_no', 'receipt_no', 'approved_at']);
        });
    }
};
