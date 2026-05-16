<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mass_schedule_assignments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('mass_schedule_id')
                ->constrained('mass_schedules')
                ->cascadeOnDelete();

            $table->string('role_name'); // e.g. celebrant, reader, choir

            // polymorphic relation
            $table->string('assignable_type');
            $table->unsignedBigInteger('assignable_id');

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['assignable_type', 'assignable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mass_schedule_assignments');
    }
};