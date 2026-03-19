<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignUuid('team_membership_id')->constrained('team_memberships')->cascadeOnDelete();
            $table->string('rsvp_status')->default('pending');
            $table->text('rsvp_note')->nullable();
            $table->foreignUuid('responded_by')->nullable()->constrained('users');
            $table->timestamp('responded_at')->nullable();
            $table->string('actual_status')->nullable();
            $table->foreignUuid('checked_by')->nullable()->constrained('users');
            $table->timestamp('checked_at')->nullable();

            $table->unique(['event_id', 'team_membership_id']);
            $table->index('rsvp_status');
            $table->index('actual_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
