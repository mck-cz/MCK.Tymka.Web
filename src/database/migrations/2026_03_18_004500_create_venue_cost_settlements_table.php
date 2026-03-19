<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('venue_cost_settlements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('venue_cost_id')->constrained('venue_costs')->cascadeOnDelete();
            $table->date('period_from');
            $table->date('period_to');
            $table->integer('total_events');
            $table->decimal('total_cost', 10, 2);
            $table->integer('total_attendances');
            $table->decimal('cost_per_attendance', 10, 2);
            $table->string('status')->default('draft');
            $table->timestamp('generated_at');
            $table->timestamp('sent_at')->nullable();
            $table->foreignUuid('created_by')->constrained('users');

            $table->index('venue_cost_id');
            $table->index('status');
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venue_cost_settlements');
    }
};
