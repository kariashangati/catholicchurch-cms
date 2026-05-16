<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('budget_estimate_expenses', function (Blueprint $table) {
            $table->id();
            $table->string('category_code', 50)->nullable()->index();
            $table->string('category_name');
            $table->string('group_name', 100)->default('general')->index();
            $table->decimal('amount', 15, 2)->default(0);
            $table->unsignedInteger('budget_year')->index();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['category_name', 'budget_year'], 'budget_expense_name_year_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_estimate_expenses');
    }
};
