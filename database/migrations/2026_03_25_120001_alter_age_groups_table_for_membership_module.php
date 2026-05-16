<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('age_groups', function (Blueprint $table) {
            if (! Schema::hasColumn('age_groups', 'gender_scope')) {
                $table->string('gender_scope', 20)->default('all')->after('max_age');
            }

            if (! Schema::hasColumn('age_groups', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('age_groups', function (Blueprint $table) {
            if (Schema::hasColumn('age_groups', 'gender_scope')) {
                $table->dropColumn('gender_scope');
            }

            if (Schema::hasColumn('age_groups', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }
};
