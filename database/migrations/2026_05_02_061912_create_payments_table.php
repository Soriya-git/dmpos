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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();

            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_method_id')->nullable()->constrained()->nullOnDelete();

            $table->string('payment_no')->unique();

            $table->enum('status', [
                'draft',
                'paid',
                'partial',
                'pay_later',
                'cancelled',
            ])->default('draft');

            $table->string('currency', 3)->default('USD');

            // Amount in payment currency
            $table->decimal('amount_paid', 12, 2)->default(0);

            // Snapshot conversion
            $table->decimal('exchange_rate_snapshot', 12, 4)->default(4100);
            $table->decimal('amount_usd_equivalent', 12, 2)->default(0);
            $table->decimal('amount_khr_equivalent', 14, 2)->default(0);

            $table->dateTime('paid_at')->nullable();

            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();

            $table->text('cancel_reason')->nullable();
            $table->text('note')->nullable();

            $table->timestamps();

            $table->index(['branch_id', 'status'], 'pay_branch_status_idx');
            $table->index(['invoice_id'], 'pay_invoice_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
