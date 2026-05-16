<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'member_id')) {
                $table->unique('member_id', 'users_member_id_unique');
            }

            if (Schema::hasColumn('users', 'kanda_id')) {
                $table->index('kanda_id', 'users_kanda_id_index');
            }

            if (Schema::hasColumn('users', 'jumuiya_id')) {
                $table->index('jumuiya_id', 'users_jumuiya_id_index');
            }

            if (Schema::hasColumn('users', 'is_active')) {
                $table->index('is_active', 'users_is_active_index');
            }

            if (Schema::hasColumn('users', 'locale')) {
                $table->index('locale', 'users_locale_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            try {
                $table->dropUnique('users_member_id_unique');
            } catch (\Throwable $e) {
            }

            try {
                $table->dropIndex('users_kanda_id_index');
            } catch (\Throwable $e) {
            }

            try {
                $table->dropIndex('users_jumuiya_id_index');
            } catch (\Throwable $e) {
            }

            try {
                $table->dropIndex('users_is_active_index');
            } catch (\Throwable $e) {
            }

            try {
                $table->dropIndex('users_locale_index');
            } catch (\Throwable $e) {
            }
        });
    }
};