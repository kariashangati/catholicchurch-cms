<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('receipt_action_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receipt_issue_id')->constrained('receipt_issues')->cascadeOnDelete();
            $table->string('action', 50);
            $table->string('from_status', 50)->nullable();
            $table->string('to_status', 50)->nullable();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('actor_name')->nullable();
            $table->text('reason')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('acted_at')->useCurrent();
            $table->timestamps();

            $table->index(['receipt_issue_id', 'action']);
            $table->index('acted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receipt_action_logs');
    }
};
