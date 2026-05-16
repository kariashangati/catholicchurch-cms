<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tithes', function (Blueprint $table) {
            if (! Schema::hasColumn('tithes', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }

            $table->index(['member_id', 'contribution_date'], 'tithes_member_date_index');
            $table->index(['jumuiya_id', 'contribution_date'], 'tithes_jumuiya_date_index');
        });
    }

    public function down(): void
    {
        Schema::table('tithes', function (Blueprint $table) {
            $table->dropIndex('tithes_member_date_index');
            $table->dropIndex('tithes_jumuiya_date_index');

            if (Schema::hasColumn('tithes', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
