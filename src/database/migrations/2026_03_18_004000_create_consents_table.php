<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('consent_type_id')->constrained('consent_types')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('child_id')->nullable()->constrained('users');
            $table->boolean('granted');
            $table->foreignUuid('granted_by')->constrained('users');
            $table->timestamp('granted_at');
            $table->timestamp('revoked_at')->nullable();

            $table->index('consent_type_id');
            $table->index('user_id');
            $table->index('child_id');
            $table->index('granted_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consents');
    }
};
