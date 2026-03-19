<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instruction_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('club_id')->constrained('clubs')->cascadeOnDelete();
            $table->string('event_type');
            $table->string('name');
            $table->text('body');
            $table->integer('sort_order')->default(0);

            $table->index('event_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instruction_templates');
    }
};
