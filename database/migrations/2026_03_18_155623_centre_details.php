<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('centre_details', function (Blueprint $table) {
            $table->id();
            $table->string('centre_name')->unique();
            $table->string('address')->nullable();
            $table->string('email')->nullable();
            $table->string('region')->nullable();
            $table->string('country')->default('Tanzania');
            $table->string('diocese')->nullable();
            $table->string('telephone_1')->nullable();
            $table->string('telephone_2')->nullable();
            $table->string('telephone_3')->nullable();
            $table->string('photo')->nullable();
            $table->string('unique_code')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('centre_details');
    }
};