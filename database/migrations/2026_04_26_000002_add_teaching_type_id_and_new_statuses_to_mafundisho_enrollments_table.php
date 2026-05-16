<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('mafundisho_enrollments')) {
            return;
        }

        Schema::table('mafundisho_enrollments', function (Blueprint $table) {
            if (! Schema::hasColumn('mafundisho_enrollments', 'teaching_type_id')) {
                $table->foreignId('teaching_type_id')
                    ->nullable()
                    ->after('member_id')
                    ->constrained('teaching_types')
                    ->nullOnDelete();
            }
        });

        // Backfill teaching_type_id from the legacy hardcoded `type` column.
        if (Schema::hasTable('teaching_types') && Schema::hasColumn('mafundisho_enrollments', 'type')) {
            DB::table('teaching_types')->orderBy('id')->get(['id', 'slug'])->each(function ($teachingType) {
                DB::table('mafundisho_enrollments')
                    ->whereNull('teaching_type_id')
                    ->where('type', $teachingType->slug)
                    ->update(['teaching_type_id' => $teachingType->id]);
            });
        }

        // Keep the existing `status` varchar/enum as-is to avoid DBAL dependency and destructive enum changes.
        // The application will validate/use: continuing, completed, failed, repeated, withdrawn.
        if (Schema::hasColumn('mafundisho_enrollments', 'status')) {
            DB::table('mafundisho_enrollments')->where('status', 'active')->update(['status' => 'continuing']);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('mafundisho_enrollments')) {
            return;
        }

        if (Schema::hasColumn('mafundisho_enrollments', 'status')) {
            DB::table('mafundisho_enrollments')->where('status', 'continuing')->update(['status' => 'active']);
        }

        Schema::table('mafundisho_enrollments', function (Blueprint $table) {
            if (Schema::hasColumn('mafundisho_enrollments', 'teaching_type_id')) {
                $table->dropConstrainedForeignId('teaching_type_id');
            }
        });
    }
};
