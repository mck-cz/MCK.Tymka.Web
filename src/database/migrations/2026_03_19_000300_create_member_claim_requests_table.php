<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_claim_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('placeholder_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('club_id')->constrained('clubs')->cascadeOnDelete();
            $table->foreignUuid('team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->foreignUuid('requested_by')->constrained('users');
            $table->string('target_email')->nullable();
            $table->string('target_phone')->nullable();
            $table->string('token')->unique();
            $table->string('link_type'); // guardian_invite / self_invite / claim_existing
            $table->foreignUuid('matched_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('pending'); // pending / accepted / declined / expired
            $table->timestamp('expires_at');
            $table->timestamp('accepted_at')->nullable();
            $table->foreignUuid('accepted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['club_id']);
            $table->index(['placeholder_id']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_claim_requests');
    }
};
