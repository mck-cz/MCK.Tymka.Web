<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absence_period_members', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('absence_period_id')->constrained('absence_periods')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('team_membership_id')->nullable()->constrained('team_memberships')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absence_period_members');
    }
};
