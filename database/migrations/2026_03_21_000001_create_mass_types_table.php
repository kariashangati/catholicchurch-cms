<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mass_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('mass_types')->insert([
            ['name' => 'Misa ya Kwanza', 'slug' => 'misa-ya-kwanza', 'description' => 'Ibada ya kwanza ya misa', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Misa ya Pili', 'slug' => 'misa-ya-pili', 'description' => 'Ibada ya pili ya misa', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Dominika', 'slug' => 'dominika', 'description' => 'Misa ya jumapili au sikukuu', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('mass_types');
    }
};
