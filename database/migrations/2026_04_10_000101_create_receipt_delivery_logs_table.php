<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('receipt_delivery_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receipt_issue_id')->constrained('receipt_issues')->cascadeOnDelete();
            $table->string('channel', 30)->default('sms');
            $table->string('recipient', 40)->nullable();
            $table->string('template_key')->nullable();
            $table->text('message_snapshot')->nullable();
            $table->string('provider', 60)->nullable();
            $table->string('provider_message_id')->nullable();
            $table->string('status', 30)->default('pending');
            $table->text('provider_response')->nullable();
            $table->foreignId('sent_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('sent_at')->nullable();
            $table->unsignedInteger('attempt_no')->default(1);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['receipt_issue_id', 'channel', 'status'], 'rdl_receipt_channel_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receipt_delivery_logs');
    }
};
