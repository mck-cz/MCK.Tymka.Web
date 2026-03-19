<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_field_values', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('definition_id')->constrained('custom_field_definitions')->cascadeOnDelete();
            $table->uuid('entity_id'); // member user_id (or event/team id in future)
            $table->text('value')->nullable();
            $table->foreignUuid('updated_by')->constrained('users');
            $table->timestamp('updated_at')->useCurrent();

            $table->unique(['definition_id', 'entity_id']);
            $table->index(['entity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_field_values');
    }
};
