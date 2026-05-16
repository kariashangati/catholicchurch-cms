<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('main_offerings', function (Blueprint $table) {
            $table->id();
            $table->decimal('amount', 14, 2)->default(0);
            $table->date('offering_date');
            $table->string('mass_name')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('approved');
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['offering_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('main_offerings');
    }
};