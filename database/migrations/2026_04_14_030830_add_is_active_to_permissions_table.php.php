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
        Schema::table('permissions', function (Blueprint $table) {
            if (! Schema::hasColumn('permissions', 'is_active')) {
                $table->boolean('is_active')
                    ->default(true)
                    ->after('guard_name')
                    ->index();
            }

            if (! Schema::hasColumn('permissions', 'description')) {
                $table->string('description', 255)
                    ->nullable()
                    ->after('is_active');
            }

            if (! Schema::hasColumn('permissions', 'module')) {
                $table->string('module', 100)
                    ->nullable()
                    ->after('description')
                    ->index();
            }
        });

        DB::table('permissions')
            ->whereNull('module')
            ->orderBy('id')
            ->get(['id', 'name'])
            ->each(function ($permission): void {
                $module = str_contains($permission->name, '.')
                    ? explode('.', $permission->name)[0]
                    : 'general';

                DB::table('permissions')
                    ->where('id', $permission->id)
                    ->update([
                        'module' => $module,
                    ]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            if (Schema::hasColumn('permissions', 'module')) {
                $table->dropColumn('module');
            }

            if (Schema::hasColumn('permissions', 'description')) {
                $table->dropColumn('description');
            }

            if (Schema::hasColumn('permissions', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }
};