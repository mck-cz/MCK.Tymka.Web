<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('albums', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('club_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('team_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('event_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->foreignUuid('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('created_at');

            $table->index('club_id');
            $table->index('team_id');
            $table->index('event_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('albums');
    }
};
