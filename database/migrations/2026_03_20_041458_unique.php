<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('familias', function (Blueprint $table) {
            $table->unique(['jumuiya_id', 'name'], 'familias_jumuiya_name_unique');
        });
    }

    public function down(): void
    {
        Schema::table('familias', function (Blueprint $table) {
            $table->dropUnique('familias_jumuiya_name_unique');
        });
    }
};