<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mass_schedule_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mass_schedule_id')->constrained('mass_schedules')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('role_name');
            $table->string('assignable_type');
            $table->unsignedBigInteger('assignable_id');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['assignable_type', 'assignable_id']);
            $table->index(['mass_schedule_id', 'role_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mass_schedule_assignments');
    }
};
