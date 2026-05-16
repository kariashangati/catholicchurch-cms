<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('apostolic_groups', function (Blueprint $table) {
            if (!Schema::hasColumn('apostolic_groups', 'code')) {
                $table->string('code', 50)->nullable()->unique()->after('name');
            }

            if (!Schema::hasColumn('apostolic_groups', 'leader_member_id')) {
                $table->foreignId('leader_member_id')->nullable()->after('is_active')
                    ->constrained('members')->nullOnDelete();
            }

            if (!Schema::hasColumn('apostolic_groups', 'assistant_leader_member_id')) {
                $table->foreignId('assistant_leader_member_id')->nullable()->after('leader_member_id')
                    ->constrained('members')->nullOnDelete();
            }

            if (!Schema::hasColumn('apostolic_groups', 'patron_member_id')) {
                $table->foreignId('patron_member_id')->nullable()->after('assistant_leader_member_id')
                    ->constrained('members')->nullOnDelete();
            }

            if (!Schema::hasColumn('apostolic_groups', 'membership_rule_type')) {
                $table->string('membership_rule_type', 50)->default('manual')->after('patron_member_id');
            }

            if (!Schema::hasColumn('apostolic_groups', 'membership_rule_value')) {
                $table->string('membership_rule_value', 100)->nullable()->after('membership_rule_type');
            }

            if (!Schema::hasColumn('apostolic_groups', 'image')) {
                $table->string('image')->nullable()->after('membership_rule_value');
            }

            if (!Schema::hasColumn('apostolic_groups', 'founded_on')) {
                $table->date('founded_on')->nullable()->after('image');
            }

            if (!Schema::hasColumn('apostolic_groups', 'meeting_day')) {
                $table->string('meeting_day', 30)->nullable()->after('founded_on');
            }

            if (!Schema::hasColumn('apostolic_groups', 'meeting_time')) {
                $table->time('meeting_time')->nullable()->after('meeting_day');
            }

            if (!Schema::hasColumn('apostolic_groups', 'meeting_location')) {
                $table->string('meeting_location')->nullable()->after('meeting_time');
            }
        });
    }

    public function down(): void
    {
        Schema::table('apostolic_groups', function (Blueprint $table) {
            $columns = [
                'leader_member_id',
                'assistant_leader_member_id',
                'patron_member_id',
                'membership_rule_type',
                'membership_rule_value',
                'image',
                'founded_on',
                'meeting_day',
                'meeting_time',
                'meeting_location',
                'code',
            ];

            foreach (['leader_member_id', 'assistant_leader_member_id', 'patron_member_id'] as $foreign) {
                if (Schema::hasColumn('apostolic_groups', $foreign)) {
                    $table->dropForeign([$foreign]);
                }
            }

            foreach ($columns as $column) {
                if (Schema::hasColumn('apostolic_groups', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};