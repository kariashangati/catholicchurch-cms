<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('communication_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('communication_campaigns')->cascadeOnDelete();
            $table->foreignId('template_id')->nullable()->constrained('communication_templates')->nullOnDelete();
            $table->foreignId('member_id')->nullable()->constrained('members')->nullOnDelete();
            $table->foreignId('familia_id')->nullable()->constrained('familias')->nullOnDelete();
            $table->foreignId('jumuiya_id')->nullable()->constrained('jumuiyas')->nullOnDelete();
            $table->foreignId('kanda_id')->nullable()->constrained('kandas')->nullOnDelete();
            $table->foreignId('apostolic_group_id')->nullable()->constrained('apostolic_groups')->nullOnDelete();
            $table->foreignId('leadership_assignment_id')->nullable()->constrained('leadership_assignments')->nullOnDelete();
            $table->string('recipient_name')->nullable();
            $table->string('recipient_phone', 30);
            $table->string('recipient_phone_normalized', 30)->index();
            $table->string('recipient_type', 50)->default('member')->index();
            $table->string('locale', 10)->nullable();
            $table->text('message_body');
            $table->unsignedSmallInteger('segment_count')->default(1);
            $table->string('status', 30)->default('pending')->index();
            $table->string('delivery_status', 30)->nullable()->index();
            $table->string('provider', 30)->default('beem');
            $table->string('provider_message_id')->nullable()->index();
            $table->string('provider_batch_id')->nullable()->index();
            $table->string('provider_status_code')->nullable();
            $table->string('provider_status_text')->nullable();
            $table->string('error_code')->nullable();
            $table->text('error_message')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('queued_at')->nullable();
            $table->timestamp('sent_at')->nullable()->index();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();

            $table->index(['campaign_id', 'status']);
            $table->index(['member_id', 'sent_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communication_messages');
    }
};
