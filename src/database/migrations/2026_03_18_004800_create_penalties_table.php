<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penalties', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('penalty_rule_id')->constrained('penalty_rules')->cascadeOnDelete();
            $table->foreignUuid('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('trigger_type');
            $table->string('original_rsvp')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->boolean('count_as_attendance')->default(false);
            $table->boolean('waived')->default(false);
            $table->foreignUuid('waived_by')->nullable()->constrained('users');
            $table->string('waived_reason')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('penalty_rule_id');
            $table->index('event_id');
            $table->index('user_id');
            $table->index('trigger_type');
            $table->index('waived');
            $table->index('waived_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penalties');
    }
};
