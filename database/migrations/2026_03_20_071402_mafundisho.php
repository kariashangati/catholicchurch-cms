<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mafundisho_enrollments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('member_id')
                ->constrained('members')
                ->cascadeOnDelete();

            $table->string('type', 30); // komunio, kipaimara, ndoa
            $table->year('year');
            $table->string('status', 30)->default('active'); // active, completed, withdrawn

            $table->date('started_at')->nullable();
            $table->date('ended_at')->nullable();

            $table->string('partner_name')->nullable();
            $table->string('partner_jumuiya')->nullable();
            $table->string('partner_phone')->nullable();

            $table->text('notes')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->unique(['member_id', 'type', 'year'], 'maf_unique_member_type_year');
            $table->index(['type', 'year']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mafundisho_enrollments');
    }
};