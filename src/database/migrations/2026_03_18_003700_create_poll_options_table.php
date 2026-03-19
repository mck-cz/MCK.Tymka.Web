<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('poll_options', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('post_id')->constrained('team_posts')->cascadeOnDelete();
            $table->string('label');
            $table->integer('sort_order')->default(0);

            $table->index('post_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('poll_options');
    }
};
