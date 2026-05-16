<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('receipt_exception_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('receipt_issue_id')->nullable()->constrained('receipt_issues')->nullOnDelete();
            $table->string('exception_type', 100);
            $table->string('severity', 20)->default('medium');
            $table->text('message');
            $table->json('context')->nullable();
            $table->boolean('is_resolved')->default(false);
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['exception_type', 'severity']);
            $table->index(['is_resolved', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receipt_exception_logs');
    }
};
