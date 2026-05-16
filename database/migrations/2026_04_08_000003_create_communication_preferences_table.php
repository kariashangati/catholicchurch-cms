<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('communication_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->unique()->constrained('members')->cascadeOnDelete();
            $table->string('preferred_locale', 10)->nullable();
            $table->string('preferred_phone', 30)->nullable();
            $table->string('alternate_phone', 30)->nullable();
            $table->boolean('allow_sms')->default(true)->index();
            $table->boolean('allow_general_sms')->default(true);
            $table->boolean('allow_finance_sms')->default(true);
            $table->boolean('allow_reminder_sms')->default(true);
            $table->boolean('allow_announcement_sms')->default(true);
            $table->boolean('allow_automated_sms')->default(true);
            $table->boolean('allow_manual_sms')->default(true);
            $table->boolean('is_phone_verified')->default(false);
            $table->timestamp('phone_verified_at')->nullable();
            $table->timestamp('opted_out_at')->nullable()->index();
            $table->string('opt_out_reason')->nullable();
            $table->timestamp('last_message_sent_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communication_preferences');
    }
};
