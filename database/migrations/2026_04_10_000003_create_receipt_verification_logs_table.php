<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receipt_verification_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('receipt_issue_id')->nullable()->constrained('receipt_issues')->nullOnDelete();
            $table->string('lookup_value', 191)->nullable();
            $table->string('lookup_type', 40)->default('token');
            $table->string('status', 30)->default('verified');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index(['lookup_type', 'lookup_value']);
            $table->index(['status', 'verified_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receipt_verification_logs');
    }
};
