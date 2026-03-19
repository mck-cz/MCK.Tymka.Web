<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('nickname')->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('phone')->nullable();
            $table->string('password')->nullable();
            $table->string('avatar_path')->nullable();
            $table->string('address')->nullable();
            $table->date('birth_date')->nullable();
            $table->boolean('is_minor')->default(false);
            $table->boolean('can_self_manage')->default(false);
            $table->string('locale')->default('cs');
            $table->json('notification_preferences')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->index('is_minor');
            $table->index('locale');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
