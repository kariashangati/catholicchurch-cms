<?php

use App\Models\ContactMessage;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contact_messages', function (Blueprint $table): void {
            if (! Schema::hasColumn('contact_messages', 'priority')) {
                $table->string('priority', 20)->default(ContactMessage::PRIORITY_NORMAL)->after('status');
            }
            if (! Schema::hasColumn('contact_messages', 'assigned_to')) {
                $table->foreignId('assigned_to')->nullable()->after('priority')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('contact_messages', 'admin_note')) {
                $table->text('admin_note')->nullable()->after('assigned_to');
            }
            if (! Schema::hasColumn('contact_messages', 'answer_message')) {
                $table->text('answer_message')->nullable()->after('admin_note');
            }
            if (! Schema::hasColumn('contact_messages', 'answered_by')) {
                $table->foreignId('answered_by')->nullable()->after('answer_message')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('contact_messages', 'answered_at')) {
                $table->timestamp('answered_at')->nullable()->after('answered_by');
            }
            if (! Schema::hasColumn('contact_messages', 'closed_by')) {
                $table->foreignId('closed_by')->nullable()->after('answered_at')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('contact_messages', 'closed_at')) {
                $table->timestamp('closed_at')->nullable()->after('closed_by');
            }
            if (! Schema::hasColumn('contact_messages', 'last_sms_status')) {
                $table->string('last_sms_status', 30)->nullable()->after('closed_at');
            }
            if (! Schema::hasColumn('contact_messages', 'last_sms_error')) {
                $table->text('last_sms_error')->nullable()->after('last_sms_status');
            }
            if (! Schema::hasColumn('contact_messages', 'last_sms_sent_at')) {
                $table->timestamp('last_sms_sent_at')->nullable()->after('last_sms_error');
            }
        });

        DB::table('contact_messages')->where('status', 'new')->update(['status' => ContactMessage::STATUS_NEW]);
        DB::table('contact_messages')->where('status', 'read')->update(['status' => ContactMessage::STATUS_READ]);
        DB::table('contact_messages')->where('status', 'answered')->update(['status' => ContactMessage::STATUS_ANSWERED]);
        DB::table('contact_messages')->where('status', 'closed')->update(['status' => ContactMessage::STATUS_CLOSED]);

        Schema::create('contact_message_replies', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('contact_message_id')->constrained('contact_messages')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reply_body');
            $table->boolean('send_sms')->default(false);
            $table->string('sms_status', 30)->nullable();
            $table->text('sms_error')->nullable();
            $table->timestamp('sms_sent_at')->nullable();
            $table->json('provider_response')->nullable();
            $table->timestamps();
        });

        Schema::create('contact_message_status_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('contact_message_id')->constrained('contact_messages')->cascadeOnDelete();
            $table->string('old_status', 30)->nullable();
            $table->string('new_status', 30);
            $table->text('note')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_message_status_logs');
        Schema::dropIfExists('contact_message_replies');

        Schema::table('contact_messages', function (Blueprint $table): void {
            foreach ([
                'last_sms_sent_at', 'last_sms_error', 'last_sms_status',
                'closed_at', 'closed_by', 'answered_at', 'answered_by',
                'answer_message', 'admin_note', 'assigned_to', 'priority',
            ] as $column) {
                if (Schema::hasColumn('contact_messages', $column)) {
                    if (in_array($column, ['assigned_to', 'answered_by', 'closed_by'], true)) {
                        $table->dropConstrainedForeignId($column);
                    } else {
                        $table->dropColumn($column);
                    }
                }
            }
        });
    }
};
