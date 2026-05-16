<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('site_settings', 'is_editable')) {
                $table->boolean('is_editable')
                    ->default(true)
                    ->after('is_public')
                    ->index();
            }

            if (! Schema::hasColumn('site_settings', 'sort_order')) {
                $table->integer('sort_order')
                    ->default(0)
                    ->after('is_editable')
                    ->index();
            }

            $table->index('group_name', 'site_settings_group_name_index');
            $table->index('setting_key', 'site_settings_setting_key_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            try {
                $table->dropIndex('site_settings_group_name_index');
            } catch (\Throwable $e) {
            }

            try {
                $table->dropIndex('site_settings_setting_key_index');
            } catch (\Throwable $e) {
            }

            if (Schema::hasColumn('site_settings', 'sort_order')) {
                $table->dropColumn('sort_order');
            }

            if (Schema::hasColumn('site_settings', 'is_editable')) {
                $table->dropColumn('is_editable');
            }
        });
    }
};