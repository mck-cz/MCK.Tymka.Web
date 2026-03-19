<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('venue_cost_member_shares', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('settlement_id')->constrained('venue_cost_settlements')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->integer('attendance_count');
            $table->decimal('amount_due', 10, 2);
            $table->string('variable_symbol');
            $table->string('qr_payload')->nullable();
            $table->string('status')->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->foreignUuid('confirmed_by')->nullable()->constrained('users');

            $table->index('settlement_id');
            $table->index('user_id');
            $table->index('status');
            $table->index('confirmed_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venue_cost_member_shares');
    }
};
