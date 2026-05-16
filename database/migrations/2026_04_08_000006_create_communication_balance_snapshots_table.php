<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('communication_balance_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 30)->default('beem')->index();
            $table->decimal('balance_units', 14, 2)->default(0);
            $table->string('currency', 10)->nullable();
            $table->json('raw_response')->nullable();
            $table->timestamp('fetched_at')->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communication_balance_snapshots');
    }
};
