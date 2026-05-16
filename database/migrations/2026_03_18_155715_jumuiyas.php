<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('jumuiyas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kanda_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('comment')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['kanda_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jumuiyas');
    }
};