<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recurrence_exclusions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('recurrence_rule_id')->constrained('recurrence_rules')->cascadeOnDelete();
            $table->date('excluded_date');
            $table->string('reason')->nullable();
            $table->foreignUuid('created_by')->constrained('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recurrence_exclusions');
    }
};
