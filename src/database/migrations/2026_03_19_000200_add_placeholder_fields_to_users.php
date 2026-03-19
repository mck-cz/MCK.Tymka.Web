<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('sex')->nullable()->after('birth_date');
            $table->string('status')->default('active')->after('can_self_manage');
            $table->foreignUuid('claimed_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            $table->timestamp('claimed_at')->nullable()->after('claimed_by');
            $table->string('created_by_role')->nullable()->after('claimed_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['claimed_by']);
            $table->dropColumn(['sex', 'status', 'claimed_by', 'claimed_at', 'created_by_role']);
        });
    }
};
