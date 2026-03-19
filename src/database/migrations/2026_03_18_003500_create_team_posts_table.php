<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_posts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->string('post_type')->default('message');
            $table->timestamp('created_at')->useCurrent();

            $table->index('team_id');
            $table->index('user_id');
            $table->index('post_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_posts');
    }
};
