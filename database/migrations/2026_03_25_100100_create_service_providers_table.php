<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('service_providers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_category_id')->constrained('service_categories')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('member_id')->nullable()->constrained('members')->nullOnDelete();
            $table->string('name'); $table->string('phone', 50); $table->string('email')->nullable(); $table->string('address', 500); $table->text('notes')->nullable(); $table->string('status', 30)->default('active'); $table->boolean('is_internal')->default(false); $table->boolean('is_active')->default(true); $table->timestamps();
            $table->index(['service_category_id','status']);
        });
    }
    public function down(): void { Schema::dropIfExists('service_providers'); }
};
