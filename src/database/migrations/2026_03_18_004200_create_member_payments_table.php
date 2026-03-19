<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('payment_request_id')->constrained('payment_requests')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('child_id')->nullable()->constrained('users');
            $table->string('variable_symbol')->unique();
            $table->decimal('amount', 10, 2);
            $table->string('status')->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->foreignUuid('confirmed_by')->nullable()->constrained('users');
            $table->timestamp('thanked_at')->nullable();
            $table->text('qr_payload')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('payment_request_id');
            $table->index('user_id');
            $table->index('child_id');
            $table->index('status');
            $table->index('confirmed_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_payments');
    }
};
