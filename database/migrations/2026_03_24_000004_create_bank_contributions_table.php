<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('bank_contributions')) {
            return;
        }

        Schema::create('bank_contributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->foreignId('familia_id')->nullable()->constrained('familias')->nullOnDelete();
            $table->foreignId('jumuiya_id')->nullable()->constrained('jumuiyas')->nullOnDelete();
            $table->foreignId('kanda_id')->nullable()->constrained('kandas')->nullOnDelete();
            $table->foreignId('contribution_type_id')->constrained('contribution_types')->restrictOnDelete();
            $table->foreignId('bank_account_id')->constrained('bank_accounts')->restrictOnDelete();
            $table->decimal('amount', 14, 2);
            $table->date('contribution_date');
            $table->string('reference_no')->unique();
            $table->string('receipt_no')->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 30)->default('pending');
            $table->foreignId('recorded_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['contribution_date', 'status']);
            $table->index(['jumuiya_id', 'contribution_date']);
            $table->index(['kanda_id', 'contribution_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_contributions');
    }
};
