<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clubs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('primary_sport')->nullable();
            $table->string('address')->nullable();
            $table->string('logo_url')->nullable();
            $table->string('color')->nullable();
            $table->string('bank_account')->nullable();
            $table->json('settings')->nullable();
            $table->string('billing_plan')->default('starter');
            $table->timestamps();

            $table->index('primary_sport');
            $table->index('billing_plan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clubs');
    }
};
