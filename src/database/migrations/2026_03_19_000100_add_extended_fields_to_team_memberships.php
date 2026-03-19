<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('team_memberships', function (Blueprint $table) {
            $table->integer('jersey_number')->nullable()->after('position');
            $table->string('federation_id')->nullable()->after('jersey_number');
            $table->string('federation_status')->nullable()->after('federation_id');
            $table->date('federation_registered_at')->nullable()->after('federation_status');
            $table->date('federation_membership_valid_until')->nullable()->after('federation_registered_at');
            $table->string('federation_link_type')->nullable()->after('federation_membership_valid_until');
            $table->string('federation_external_url')->nullable()->after('federation_link_type');
            $table->string('license_type')->nullable()->after('federation_external_url');
            $table->date('license_valid_until')->nullable()->after('license_type');
            $table->boolean('attendance_required')->default(true)->after('license_valid_until');
        });
    }

    public function down(): void
    {
        Schema::table('team_memberships', function (Blueprint $table) {
            $table->dropColumn([
                'jersey_number',
                'federation_id',
                'federation_status',
                'federation_registered_at',
                'federation_membership_valid_until',
                'federation_link_type',
                'federation_external_url',
                'license_type',
                'license_valid_until',
                'attendance_required',
            ]);
        });
    }
};
