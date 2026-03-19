<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('club_id')->constrained('clubs')->cascadeOnDelete();
            $table->foreignUuid('team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->foreignUuid('created_by')->constrained('users');
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('currency')->default('CZK');
            $table->string('payment_type');
            $table->date('due_date');
            $table->string('variable_symbol_prefix');
            $table->string('bank_account');
            $table->string('status')->default('active');
            $table->timestamp('created_at')->useCurrent();

            $table->index('club_id');
            $table->index('team_id');
            $table->index('created_by');
            $table->index('status');
            $table->index('payment_type');
            $table->index('due_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_requests');
    }
};
