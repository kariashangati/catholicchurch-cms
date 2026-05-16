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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('log_name', 50)->default('activity')->index();
            $table->string('event', 50)->index(); // created, updated, deleted, login, logout, failed_login, issued, reprinted...
            $table->string('module', 100)->nullable()->index(); // auth, members, finance, receipts, communication...
            $table->string('action', 150)->nullable()->index(); // human/business action label

            $table->nullableMorphs('subject'); // subject_type, subject_id

            $table->string('subject_label')->nullable()->index();
            $table->text('description')->nullable();

            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->json('properties')->nullable();

            $table->string('ip_address', 45)->nullable()->index();
            $table->text('user_agent')->nullable();
            $table->string('browser', 100)->nullable()->index();
            $table->string('platform', 100)->nullable()->index();
            $table->string('device_type', 50)->nullable()->index();

            $table->string('method', 10)->nullable()->index();
            $table->string('route_name')->nullable()->index();
            $table->text('url')->nullable();

            $table->string('risk_level', 20)->default('low')->index(); // low, medium, high, critical
            $table->timestamp('occurred_at')->nullable()->index();

            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['module', 'created_at']);
            $table->index(['event', 'created_at']);
            $table->index(['risk_level', 'created_at']);
            $table->index(['subject_type', 'subject_id'], 'audit_logs_subject_lookup_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};