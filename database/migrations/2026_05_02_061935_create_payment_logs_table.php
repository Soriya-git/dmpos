<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();

            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();

            $table->string('action');
            // paid, partial_paid, pay_later, cancelled, refunded

            $table->decimal('amount_before', 14, 2)->default(0);
            $table->decimal('amount_after', 14, 2)->default(0);
            $table->decimal('amount_changed', 14, 2)->default(0);

            $table->string('currency', 3)->default('USD');
            $table->decimal('exchange_rate_snapshot', 12, 4)->default(4100);

            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();

            $table->json('payload')->nullable();
            $table->text('note')->nullable();

            $table->timestamps();

            $table->index(['branch_id', 'action'], 'payment_logs_branch_action_idx');
            $table->index(['invoice_id'], 'payment_logs_invoice_idx');
            $table->index(['payment_id'], 'payment_logs_payment_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_logs');
    }
};
