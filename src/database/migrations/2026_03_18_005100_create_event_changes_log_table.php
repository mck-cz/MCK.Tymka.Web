<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_changes_log', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignUuid('changed_by')->constrained('users');
            $table->string('field_name');
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->timestamp('changed_at');

            $table->index('event_id');
            $table->index('changed_by');
            $table->index('field_name');
            $table->index('changed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_changes_log');
    }
};
