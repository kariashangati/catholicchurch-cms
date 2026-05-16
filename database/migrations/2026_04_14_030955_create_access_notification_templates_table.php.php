<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('access_notification_templates', function (Blueprint $table) {
            $table->id();

            $table->string('code', 100)->unique();
            $table->string('name', 150);
            $table->string('channel', 30)->default('sms')->index();
            $table->string('locale', 10)->default('sw')->index();

            $table->string('subject', 255)->nullable();
            $table->text('message');

            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_default')->default(false)->index();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->unique(['code', 'locale'], 'access_notification_templates_code_locale_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('access_notification_templates');
    }
};