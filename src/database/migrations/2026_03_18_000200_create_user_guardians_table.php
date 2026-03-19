<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_guardians', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('guardian_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('child_id')->constrained('users')->cascadeOnDelete();
            $table->string('relationship');
            $table->boolean('is_primary')->default(false);
            $table->timestamp('created_at')->nullable();

            $table->index('guardian_id');
            $table->index('child_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_guardians');
    }
};
