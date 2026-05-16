<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('contribution_type_plans')) {
            return;
        }

        Schema::create('contribution_type_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contribution_type_id')->constrained('contribution_types')->cascadeOnDelete();
            $table->decimal('target_amount', 14, 2)->default(0);
            $table->unsignedInteger('installments_count')->default(1);
            $table->date('due_date')->nullable();
            $table->timestamps();
            $table->unique('contribution_type_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contribution_type_plans');
    }
};
