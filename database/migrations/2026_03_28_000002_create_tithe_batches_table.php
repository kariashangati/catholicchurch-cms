<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tithe_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kanda_id')->nullable()->constrained('kandas')->nullOnDelete();
            $table->foreignId('jumuiya_id')->constrained('jumuiyas')->cascadeOnDelete();
            $table->date('contribution_date');
            $table->unsignedSmallInteger('tithe_year');
            $table->unsignedTinyInteger('tithe_month');
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->unsignedInteger('rows_count')->default(0);
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['jumuiya_id', 'tithe_year', 'tithe_month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tithe_batches');
    }
};
