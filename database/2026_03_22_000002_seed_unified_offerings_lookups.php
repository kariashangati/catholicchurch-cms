<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $now = now();
        DB::table('offering_types')->updateOrInsert(['slug' => 'mass-offering'], ['name' => 'Sadaka ya Misa', 'description' => 'Sadaka inayotolewa kwenye misa', 'category' => 'mass', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now]);
        DB::table('offering_types')->updateOrInsert(['slug' => 'main-offering'], ['name' => 'Sadaka Kuu', 'description' => 'Sadaka kuu ya parokia', 'category' => 'general', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now]);
        DB::table('offering_types')->updateOrInsert(['slug' => 'community-offering'], ['name' => 'Sadaka ya Jumuiya', 'description' => 'Sadaka ya jumuiya', 'category' => 'community', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now]);
    }
    public function down(): void
    {
        DB::table('offering_types')->whereIn('slug', ['mass-offering','main-offering','community-offering'])->delete();
    }
};
