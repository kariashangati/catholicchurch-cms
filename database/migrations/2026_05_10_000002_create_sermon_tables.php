<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sermons', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('summary')->nullable();
            $table->longText('body_html')->nullable();
            $table->string('cover_image_path')->nullable();
            $table->string('video_source')->default('none');
            $table->string('video_path')->nullable();
            $table->string('video_url', 500)->nullable();
            $table->string('thumbnail_path')->nullable();
            $table->string('status', 40)->default('rasimu');
            $table->boolean('is_active')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('request_id')->nullable();
            $table->timestamps();
        });

        Schema::create('sermon_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sermon_id')->constrained('sermons')->cascadeOnDelete();
            $table->string('target_type', 40);
            $table->unsignedBigInteger('target_id')->nullable();
            $table->string('label')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('sermon_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sermon_id')->constrained('sermons')->cascadeOnDelete();
            $table->foreignId('member_id')->nullable()->constrained('members')->nullOnDelete();
            $table->string('name')->nullable();
            $table->string('phone');
            $table->string('email')->nullable();
            $table->foreignId('familia_id')->nullable()->constrained('familias')->nullOnDelete();
            $table->foreignId('jumuiya_id')->nullable()->constrained('jumuiyas')->nullOnDelete();
            $table->foreignId('kanda_id')->nullable()->constrained('kandas')->nullOnDelete();
            $table->string('sms_status', 40)->default('haijatumwa');
            $table->timestamp('sms_sent_at')->nullable();
            $table->text('sms_error')->nullable();
            $table->timestamps();
        });

        Schema::create('sermon_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sermon_id')->constrained('sermons')->cascadeOnDelete();
            $table->foreignId('sermon_recipient_id')->constrained('sermon_recipients')->cascadeOnDelete();
            $table->foreignId('member_id')->nullable()->constrained('members')->nullOnDelete();
            $table->string('token', 30)->unique();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('last_opened_at')->nullable();
            $table->unsignedInteger('open_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('sermon_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_reference')->unique();
            $table->foreignId('member_id')->nullable()->constrained('members')->nullOnDelete();
            $table->string('name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->foreignId('jumuiya_id')->nullable()->constrained('jumuiyas')->nullOnDelete();
            $table->string('topic');
            $table->text('message')->nullable();
            $table->string('preferred_response_type')->nullable();
            $table->string('status', 40)->default('limepokelewa');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('linked_sermon_id')->nullable()->constrained('sermons')->nullOnDelete();
            $table->text('admin_note')->nullable();
            $table->timestamps();
        });

        Schema::create('sermon_request_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sermon_request_id')->constrained('sermon_requests')->cascadeOnDelete();
            $table->string('old_status')->nullable();
            $table->string('new_status');
            $table->text('note')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('sermon_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sermon_id')->constrained('sermons')->cascadeOnDelete();
            $table->foreignId('sermon_access_token_id')->nullable()->constrained('sermon_access_tokens')->nullOnDelete();
            $table->foreignId('sermon_recipient_id')->nullable()->constrained('sermon_recipients')->nullOnDelete();
            $table->foreignId('member_id')->nullable()->constrained('members')->nullOnDelete();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('viewed_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sermon_views');
        Schema::dropIfExists('sermon_request_logs');
        Schema::dropIfExists('sermon_requests');
        Schema::dropIfExists('sermon_access_tokens');
        Schema::dropIfExists('sermon_recipients');
        Schema::dropIfExists('sermon_targets');
        Schema::dropIfExists('sermons');
    }
};
