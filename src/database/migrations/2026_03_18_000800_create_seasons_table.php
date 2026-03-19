<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seasons', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('club_id')->constrained('clubs')->cascadeOnDelete();
            $table->string('name');
            $table->date('start_date');
            $table->date('end_date');

            $table->index('club_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seasons');
    }
};
