<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consent_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('club_id')->constrained('clubs')->cascadeOnDelete();
            $table->string('name');
            $table->text('description');
            $table->boolean('is_required')->default(false);
            $table->integer('sort_order')->default(0);

            $table->index('club_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consent_types');
    }
};
