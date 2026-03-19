<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nominations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignUuid('team_membership_id')->constrained('team_memberships')->cascadeOnDelete();
            $table->foreignUuid('source_team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->string('status')->default('nominated');
            $table->integer('priority')->default(1);
            $table->foreignUuid('nominated_by')->constrained('users');
            $table->foreignUuid('responded_by')->nullable()->constrained('users');
            $table->timestamp('responded_at')->nullable();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nominations');
    }
};
