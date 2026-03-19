<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_results', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('event_id')->constrained()->cascadeOnDelete();
            $table->integer('score_home')->nullable();
            $table->integer('score_away')->nullable();
            $table->string('opponent_name')->nullable();
            $table->enum('result', ['win', 'loss', 'draw'])->nullable();
            $table->text('notes')->nullable();
            $table->foreignUuid('recorded_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('created_at');

            $table->index('event_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_results');
    }
};
