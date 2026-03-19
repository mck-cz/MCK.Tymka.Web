<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invitations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('club_id')->constrained('clubs')->cascadeOnDelete();
            $table->uuid('team_id')->nullable(); // FK added after teams table is created
            $table->foreignUuid('invited_by')->constrained('users');
            $table->string('email');
            $table->string('intended_role');
            $table->string('status')->default('pending');
            $table->string('token')->unique();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index('team_id');
            $table->index('email');
            $table->index('status');
            $table->index('invited_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitations');
    }
};
