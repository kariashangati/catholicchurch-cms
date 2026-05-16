<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('church_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_category_id')->constrained('asset_categories')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('name'); $table->string('asset_code', 100)->nullable()->unique(); $table->string('registration_number', 100)->nullable(); $table->decimal('acquisition_cost', 15, 2)->nullable(); $table->decimal('current_value', 15, 2)->nullable(); $table->date('acquisition_date')->nullable(); $table->string('condition_status', 30)->default('good'); $table->string('location')->nullable(); $table->string('document_path')->nullable(); $table->text('notes')->nullable(); $table->boolean('is_active')->default(true); $table->timestamps();
            $table->index(['asset_category_id','condition_status']);
        });
    }
    public function down(): void { Schema::dropIfExists('church_assets'); }
};
