<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recurrence_rules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('club_id')->constrained('clubs')->cascadeOnDelete();
            $table->foreignUuid('team_id')->constrained('teams')->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->string('event_type');
            $table->string('frequency');
            $table->integer('interval')->default(1);
            $table->integer('day_of_week');
            $table->string('week_parity')->nullable();
            $table->integer('nth_weekday')->nullable();
            $table->time('time_start');
            $table->time('time_end');
            $table->foreignUuid('venue_id')->nullable()->constrained('venues')->nullOnDelete();
            $table->string('surface_type')->nullable();
            $table->foreignUuid('instructions_template_id')->nullable()->constrained('instruction_templates')->nullOnDelete();
            $table->foreignUuid('equipment_template_id')->nullable()->constrained('equipment_templates')->nullOnDelete();
            $table->integer('auto_create_days_ahead')->default(14);
            $table->boolean('auto_rsvp')->default(true);
            $table->date('valid_from');
            $table->date('valid_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignUuid('created_by')->constrained('users');
            $table->timestamps();

            $table->index('event_type');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recurrence_rules');
    }
};
