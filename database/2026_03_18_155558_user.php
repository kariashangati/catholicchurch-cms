<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('member_id')->nullable()->after('id')->constrained('members')->nullOnDelete();
            $table->foreignId('kanda_id')->nullable()->after('member_id')->constrained()->nullOnDelete();
            $table->foreignId('jumuiya_id')->nullable()->after('kanda_id')->constrained()->nullOnDelete();
            $table->string('phone')->nullable()->after('email');
            $table->boolean('is_active')->default(true)->after('locale');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('member_id');
            $table->dropConstrainedForeignId('kanda_id');
            $table->dropConstrainedForeignId('jumuiya_id');
            $table->dropColumn(['phone', 'is_active', 'last_login_at']);
        });
    }
};