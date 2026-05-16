<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('communication_approval_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('campaign_id')->constrained('communication_campaigns')->cascadeOnDelete();
            $table->foreignId('acted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 30); // submitted, approved, rejected, cancelled, retried
            $table->text('notes')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['campaign_id', 'action']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communication_approval_histories');
    }
};
