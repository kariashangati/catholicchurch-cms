<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mass_schedules', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->foreignId('mass_type_id')
                ->constrained('mass_types')
                ->cascadeOnDelete();

            $table->dateTime('scheduled_at');

            $table->string('location')->nullable();
            $table->text('description')->nullable();

            $table->string('status')->default('scheduled'); // scheduled, completed, cancelled
            $table->boolean('special_occasion')->default(false);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mass_schedules');
    }
};