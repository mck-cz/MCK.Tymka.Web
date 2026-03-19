<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('club_id')->constrained('clubs')->cascadeOnDelete();
            $table->foreignUuid('team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->foreignUuid('venue_id')->nullable()->constrained('venues')->nullOnDelete();
            $table->string('location')->nullable();
            $table->foreignUuid('created_by')->constrained('users');
            $table->string('event_type');
            $table->string('title');
            $table->string('surface_type')->nullable();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->foreignUuid('recurrence_rule_id')->nullable()->constrained('recurrence_rules')->nullOnDelete();
            $table->dateTime('rsvp_deadline')->nullable();
            $table->dateTime('nomination_deadline')->nullable();
            $table->integer('min_capacity')->nullable();
            $table->integer('max_capacity')->nullable();
            $table->text('instructions')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('scheduled');
            $table->text('cancel_reason')->nullable();
            $table->uuid('rescheduled_to')->nullable();
            $table->foreignUuid('cancelled_by')->nullable()->constrained('users');
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['club_id', 'starts_at']);
            $table->index(['team_id', 'starts_at']);
            $table->index('status');
            $table->index('event_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
