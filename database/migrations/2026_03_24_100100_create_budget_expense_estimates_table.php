<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('budget_expense_estimates', function (Blueprint $table) {
            $table->id();
            $table->string('category_name', 150);
            $table->string('category_group', 40)->default('ordinary');
            $table->decimal('amount', 14, 2)->default(0);
            $table->unsignedSmallInteger('budget_year');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['category_name', 'budget_year'], 'budget_expense_unique_category_year');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_expense_estimates');
    }
};
