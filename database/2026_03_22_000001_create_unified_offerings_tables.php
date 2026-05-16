<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('offering_types', function (Blueprint $table) {
            $table->id(); $table->string('name', 150)->unique(); $table->string('slug', 150)->unique(); $table->string('description', 255)->nullable(); $table->string('category', 50)->default('general'); $table->boolean('is_active')->default(true); $table->timestamps();
        });
        Schema::create('mass_types', function (Blueprint $table) {
            $table->id(); $table->string('name', 150)->unique(); $table->string('slug', 150)->unique(); $table->string('description', 255)->nullable(); $table->boolean('is_active')->default(true); $table->timestamps();
        });
        Schema::create('offerings', function (Blueprint $table) {
            $table->id(); $table->foreignId('offering_type_id')->constrained('offering_types'); $table->foreignId('mass_type_id')->nullable()->constrained('mass_types')->nullOnDelete(); $table->enum('collection_scope', ['parish','kanda','jumuiya'])->default('parish'); $table->foreignId('centre_detail_id')->nullable()->constrained('centre_details')->nullOnDelete(); $table->foreignId('kanda_id')->nullable()->constrained('kandas')->nullOnDelete(); $table->foreignId('jumuiya_id')->nullable()->constrained('jumuiyas')->nullOnDelete(); $table->date('collection_date'); $table->decimal('amount', 14, 2)->default(0); $table->string('payment_method', 30)->nullable(); $table->string('reference_no', 100)->nullable(); $table->string('receipt_no', 100)->nullable(); $table->enum('status', ['pending','approved','rejected'])->default('approved'); $table->text('description')->nullable(); $table->text('notes')->nullable(); $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete(); $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete(); $table->timestamp('approved_at')->nullable(); $table->timestamps(); $table->softDeletes(); $table->index('collection_date'); $table->index(['offering_type_id','collection_date']); $table->index('collection_scope'); $table->index('status');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('offerings'); Schema::dropIfExists('mass_types'); Schema::dropIfExists('offering_types');
    }
};
