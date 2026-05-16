<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receipt_access_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('receipt_issue_id')->constrained('receipt_issues')->cascadeOnDelete();
            $table->string('access_token', 120)->nullable();
            $table->string('access_type', 40)->default('view');
            $table->string('status', 30)->default('success');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamp('accessed_at')->nullable();
            $table->timestamps();

            $table->index(['receipt_issue_id', 'access_type']);
            $table->index(['status', 'accessed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receipt_access_logs');
    }
};
