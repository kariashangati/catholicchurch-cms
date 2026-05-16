<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('receipt_delivery_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('sms_enabled')->default(false);
            $table->boolean('sms_manual_only')->default(true);
            $table->boolean('sms_auto_after_issue')->default(false);
            $table->boolean('sms_auto_after_print')->default(false);
            $table->unsignedSmallInteger('link_expiry_hours')->default(72);
            $table->string('active_sms_template_key')->default('finance.receipts.sms_link');
            $table->boolean('allow_resend')->default(true);
            $table->unsignedTinyInteger('max_resends')->default(3);
            $table->boolean('respect_member_preferences')->default(true);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receipt_delivery_settings');
    }
};
