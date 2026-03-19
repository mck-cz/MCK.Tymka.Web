<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_log', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignUuid('team_membership_id')->constrained('team_memberships')->cascadeOnDelete();
            $table->foreignUuid('changed_by')->constrained('users');
            $table->string('old_status');
            $table->string('new_status');
            $table->timestamp('changed_at');

            $table->index('event_id');
            $table->index('team_membership_id');
            $table->index('changed_by');
            $table->index('changed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_log');
    }
};
