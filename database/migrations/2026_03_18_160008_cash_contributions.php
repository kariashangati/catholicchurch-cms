<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cash_contributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contribution_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->nullable()->constrained('members')->nullOnDelete();
            $table->date('contribution_date');
            $table->decimal('amount', 14, 2)->default(0);
            $table->string('group_name')->default('michango');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('approved');
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['contribution_date', 'contribution_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_contributions');
    }
};