<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->string('phone');
            $table->decimal('amount', 12, 2);
            $table->string('currency')->default('TZS');
            $table->string('country')->default('TZN');

            $table->string('payment_reference')->unique();
            $table->string('third_party_conversation_id')->unique();

            $table->string('conversation_id')->nullable();
            $table->string('transaction_id')->nullable();

            $table->string('status')->default('pending');
            // pending, initiated, paid, failed, cancelled

            $table->json('request_payload')->nullable();
            $table->json('mpesa_response')->nullable();
            $table->json('callback_payload')->nullable();

            $table->timestamp('paid_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};