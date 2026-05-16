<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('leadership_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('leadership_position_id')->constrained('leadership_positions')->restrictOnDelete();
            $table->foreignId('kanda_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('jumuiya_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('apostolic_group_id')->nullable()->constrained()->nullOnDelete();
            $table->string('scope_type', 30)->default('parish');
            $table->string('scope_label')->nullable();
            $table->date('started_at');
            $table->date('ended_at')->nullable();
            $table->string('status', 20)->default('active');
            $table->text('notes')->nullable();
            $table->foreignId('appointed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['scope_type', 'status']);
            $table->index(['member_id', 'status']);
            $table->index(['leadership_position_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leadership_assignments');
    }
};
