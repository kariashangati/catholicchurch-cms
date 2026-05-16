<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('communication_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('channel', 30)->default('sms');
            $table->string('category', 50)->index();
            $table->string('locale', 10)->default('sw')->index();
            $table->string('subject')->nullable();
            $table->text('body');
            $table->json('variables')->nullable();
            $table->string('event_key')->nullable()->index();
            $table->string('audience_type', 50)->nullable()->index();
            $table->string('status', 20)->default('draft')->index();
            $table->boolean('is_system')->default(false);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['channel', 'status']);
            $table->index(['category', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communication_templates');
    }
};
