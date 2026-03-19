<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absence_periods', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('created_by')->constrained('users');
            $table->string('reason_type');
            $table->text('reason_note')->nullable();
            $table->date('starts_at');
            $table->date('ends_at');
            $table->json('apply_to_teams')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index('reason_type');
            $table->index(['starts_at', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absence_periods');
    }
};
