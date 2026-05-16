<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('halls', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('capacity')->nullable();
            $table->string('location')->nullable();
            $table->text('conditions')->nullable();
            $table->decimal('default_price', 15, 2)->default(0);
            $table->string('bank_name')->nullable();
            $table->string('bank_account_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->text('payment_instructions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('hall_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hall_id')->constrained('halls')->cascadeOnDelete();
            $table->string('image_path');
            $table->string('caption')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_cover')->default(false);
            $table->timestamps();
        });

        Schema::create('hall_price_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hall_id')->constrained('halls')->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week')->nullable()->comment('0 Sunday, 6 Saturday');
            $table->date('specific_date')->nullable();
            $table->decimal('price', 15, 2)->default(0);
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['hall_id', 'day_of_week', 'specific_date']);
        });

        Schema::create('hall_blocked_dates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hall_id')->constrained('halls')->cascadeOnDelete();
            $table->date('blocked_date');
            $table->string('reason')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['hall_id', 'blocked_date']);
        });

        Schema::create('hall_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hall_id')->constrained('halls')->cascadeOnDelete();
            $table->string('booking_reference')->unique();
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('customer_email')->nullable();
            $table->date('booking_date');
            $table->decimal('price', 15, 2)->default(0);
            $table->string('booking_status')->default('inasubiri');
            $table->string('payment_status')->default('haijalipwa');
            $table->string('payment_reference')->nullable();
            $table->text('payment_note')->nullable();
            $table->string('payment_proof_path')->nullable();
            $table->text('admin_note')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->index(['hall_id', 'booking_date', 'booking_status']);
            $table->index(['payment_status', 'booking_status']);
        });

        Schema::create('hall_booking_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('hall_bookings')->cascadeOnDelete();
            $table->string('old_booking_status')->nullable();
            $table->string('new_booking_status')->nullable();
            $table->string('old_payment_status')->nullable();
            $table->string('new_payment_status')->nullable();
            $table->text('note')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hall_booking_status_logs');
        Schema::dropIfExists('hall_bookings');
        Schema::dropIfExists('hall_blocked_dates');
        Schema::dropIfExists('hall_price_rules');
        Schema::dropIfExists('hall_images');
        Schema::dropIfExists('halls');
    }
};
