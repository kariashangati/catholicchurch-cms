<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('visitor_logs', function (Blueprint $table) {
            $table->id();
            $table->date('visit_date')->index();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('member_id')->nullable()->constrained()->nullOnDelete();
            $table->string('session_id', 255)->index();
            $table->string('route_name', 255)->nullable()->index();
            $table->string('request_path', 255)->nullable()->index();
            $table->string('request_method', 10)->default('GET');
            $table->string('source', 50)->default('authenticated_web')->index();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->boolean('is_dashboard')->default(false)->index();
            $table->timestamp('visited_at')->nullable()->index();
            $table->timestamps();

            $table->unique(['visit_date', 'session_id', 'route_name', 'request_path'], 'visitor_logs_daily_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitor_logs');
    }
};
