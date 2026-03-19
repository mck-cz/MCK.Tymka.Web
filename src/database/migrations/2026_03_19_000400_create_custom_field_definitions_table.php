<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_field_definitions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('club_id')->constrained('clubs')->cascadeOnDelete();
            $table->string('entity_type')->default('member'); // member / event / team
            $table->string('name');
            $table->string('display_name')->nullable();
            $table->string('field_type'); // text / textarea / number_int / number_decimal / checkbox / select / multi_select / date
            $table->json('options')->nullable();
            $table->string('default_value')->nullable();
            $table->string('placeholder')->nullable();
            $table->string('help_text')->nullable();
            $table->string('suffix')->nullable();
            $table->boolean('is_required')->default(false);
            $table->decimal('validation_min', 10, 2)->nullable();
            $table->decimal('validation_max', 10, 2)->nullable();
            $table->string('validation_regex')->nullable();
            $table->string('visibility_read')->default('everyone'); // everyone / coaches / admins
            $table->string('visibility_write')->default('coaches'); // member / coaches / admins
            $table->boolean('show_in_registration')->default(false);
            $table->boolean('show_in_roster')->default(false);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignUuid('created_by')->constrained('users');
            $table->timestamps();

            $table->index(['club_id', 'entity_type']);
            $table->index(['is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_field_definitions');
    }
};
