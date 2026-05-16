<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tithe_batch_denominations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tithe_batch_id')->constrained('tithe_batches')->cascadeOnDelete();
            $table->decimal('denomination_value', 12, 2);
            $table->unsignedInteger('quantity')->default(0);
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tithe_batch_denominations');
    }
};
