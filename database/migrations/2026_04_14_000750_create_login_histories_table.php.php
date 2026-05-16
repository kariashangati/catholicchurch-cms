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
        Schema::create('login_histories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('user_name')->nullable()->index();
            $table->string('email')->nullable()->index();

            $table->string('status', 30)->default('success')->index();
            // success, failed, logged_out, locked_out

            $table->boolean('is_suspicious')->default(false)->index();
            $table->string('risk_level', 20)->default('low')->index();
            // low, medium, high, critical

            $table->json('suspicion_reasons')->nullable();

            $table->string('ip_address', 45)->nullable()->index();
            $table->string('country', 120)->nullable()->index();
            $table->string('city', 120)->nullable()->index();

            $table->string('browser', 100)->nullable()->index();
            $table->string('platform', 100)->nullable()->index();
            $table->string('device_type', 50)->nullable()->index();
            $table->string('device_name', 150)->nullable()->index();

            $table->text('user_agent')->nullable();
            $table->string('session_id', 255)->nullable()->index();

            $table->timestamp('logged_in_at')->nullable()->index();
            $table->timestamp('logged_out_at')->nullable()->index();
            $table->timestamp('last_seen_at')->nullable()->index();

            $table->text('failure_reason')->nullable();
            $table->json('meta')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'logged_in_at']);
            $table->index(['status', 'logged_in_at']);
            $table->index(['is_suspicious', 'logged_in_at']);
            $table->index(['risk_level', 'logged_in_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('login_histories');
    }
};