<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penalty_rules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('club_id')->constrained('clubs')->cascadeOnDelete();
            $table->foreignUuid('team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->string('name');
            $table->string('trigger_type');
            $table->string('penalty_type');
            $table->decimal('amount', 10, 2)->nullable();
            $table->integer('late_cancel_hours')->nullable();
            $table->integer('grace_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignUuid('created_by')->constrained('users');
            $table->timestamps();

            $table->index('club_id');
            $table->index('team_id');
            $table->index('created_by');
            $table->index('is_active');
            $table->index('trigger_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penalty_rules');
    }
};
