<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('communication_automations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('event_key')->index();
            $table->string('channel', 30)->default('sms')->index();
            $table->foreignId('template_id')->nullable()->constrained('communication_templates')->nullOnDelete();
            $table->boolean('is_enabled')->default(false)->index();
            $table->string('trigger_mode', 30)->default('immediate');
            $table->unsignedInteger('delay_minutes')->nullable();
            $table->string('audience_type', 50)->nullable()->index();
            $table->json('conditions')->nullable();
            $table->boolean('respect_preferences')->default(true);
            $table->boolean('respect_quiet_hours')->default(false);
            $table->boolean('send_once_per_entity')->default(false);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['event_key', 'is_enabled']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communication_automations');
    }
};
