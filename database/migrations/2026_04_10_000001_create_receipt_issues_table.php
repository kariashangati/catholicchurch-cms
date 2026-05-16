<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('receipt_issues', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_no', 50)->unique();
            $table->string('receipt_type', 60);
            $table->string('source_type', 60);
            $table->unsignedBigInteger('source_id')->nullable();

            $table->foreignId('member_id')->nullable()->constrained('members')->nullOnDelete();
            $table->foreignId('familia_id')->nullable()->constrained('familias')->nullOnDelete();
            $table->foreignId('jumuiya_id')->nullable()->constrained('jumuiyas')->nullOnDelete();
            $table->foreignId('kanda_id')->nullable()->constrained('kandas')->nullOnDelete();
            $table->foreignId('contribution_type_id')->nullable()->constrained('contribution_types')->nullOnDelete();

            $table->unsignedTinyInteger('period_month')->nullable();
            $table->unsignedSmallInteger('period_year')->nullable();
            $table->decimal('amount', 14, 2);
            $table->string('currency', 3)->default('TZS');
            $table->string('status', 30)->default('issued');

            $table->timestamp('issued_at')->nullable();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('printed_at')->nullable();
            $table->foreignId('printed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('print_count')->default(0);
            $table->timestamp('downloaded_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->string('delivery_channel', 30)->nullable();

            $table->string('verification_code', 32)->nullable()->index();
            $table->string('pdf_path')->nullable();
            $table->foreignId('reissued_from_id')->nullable()->constrained('receipt_issues')->nullOnDelete();
            $table->timestamp('voided_at')->nullable();
            $table->foreignId('voided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('void_reason')->nullable();
            $table->json('meta')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'receipt_type']);
            $table->index(['source_type', 'source_id']);
            $table->index(['member_id', 'period_year', 'period_month']);
            $table->index(['jumuiya_id', 'period_year', 'period_month']);
            $table->index(['kanda_id', 'period_year', 'period_month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receipt_issues');
    }
};
