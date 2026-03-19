<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('venue_costs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('club_id')->constrained('clubs')->cascadeOnDelete();
            $table->foreignUuid('team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->string('name');
            $table->decimal('cost_per_event', 10, 2);
            $table->string('currency')->default('CZK');
            $table->string('split_method');
            $table->string('billing_period');
            $table->json('include_event_types');
            $table->string('bank_account')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignUuid('created_by')->constrained('users');
            $table->timestamps();

            $table->index('club_id');
            $table->index('team_id');
            $table->index('created_by');
            $table->index('is_active');
            $table->index('split_method');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venue_costs');
    }
};
