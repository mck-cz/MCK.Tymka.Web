<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('join_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('club_id')->constrained('clubs')->cascadeOnDelete();
            $table->uuid('team_id')->nullable(); // FK added after teams table is created
            $table->string('requested_role');
            $table->text('message')->nullable();
            $table->string('status')->default('pending');
            $table->uuid('reviewed_by')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index('team_id');
            $table->index('status');
            $table->index('reviewed_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('join_requests');
    }
};
