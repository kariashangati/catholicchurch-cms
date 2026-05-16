<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('receipt_issues', function (Blueprint $table): void {
            $table->string('access_token', 120)->nullable()->unique()->after('verification_code');
            $table->timestamp('access_token_expires_at')->nullable()->after('access_token');
            $table->boolean('link_enabled')->default(true)->after('access_token_expires_at');
            $table->timestamp('link_revoked_at')->nullable()->after('link_enabled');

            $table->timestamp('verified_at')->nullable()->after('link_revoked_at');
            $table->unsignedBigInteger('verified_by')->nullable()->after('verified_at');
            $table->timestamp('last_accessed_at')->nullable()->after('verified_by');
            $table->string('last_access_ip', 45)->nullable()->after('last_accessed_at');
            $table->text('access_notes')->nullable()->after('last_access_ip');

            $table->foreign('verified_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('receipt_issues', function (Blueprint $table): void {
            $table->dropForeign(['verified_by']);
            $table->dropColumn([
                'access_token',
                'access_token_expires_at',
                'link_enabled',
                'link_revoked_at',
                'verified_at',
                'verified_by',
                'last_accessed_at',
                'last_access_ip',
                'access_notes',
            ]);
        });
    }
};
