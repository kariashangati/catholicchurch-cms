<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('apostolic_group_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('apostolic_group_id')->constrained('apostolic_groups')->cascadeOnDelete();
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->string('role', 50)->default('member');
            $table->string('status', 30)->default('active');
            $table->date('joined_at')->nullable();
            $table->date('left_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('added_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['apostolic_group_id', 'member_id'], 'apostolic_group_member_unique');
            $table->index(['apostolic_group_id', 'status']);
            $table->index(['member_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apostolic_group_members');
    }
};