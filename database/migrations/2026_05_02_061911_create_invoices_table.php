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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pos_terminal_id')->nullable()->constrained()->nullOnDelete();
            $table->date('pos_open_date')->nullable();

            $table->foreignId('dining_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();

            $table->string('invoice_no')->unique();

            $table->enum('status', [
                'draft',
                'issued',
                'partially_paid',
                'paid',
                'pay_later',
                'cancelled',
            ])->default('draft');

            $table->string('currency', 3)->default('USD');

            // Exchange rate snapshot
            $table->decimal('exchange_rate_snapshot', 12, 4)->default(4100);

            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2)->default(0);

            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('balance_amount', 12, 2)->default(0);

            $table->dateTime('issued_at')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->dateTime('cancelled_at')->nullable();

            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();

            $table->text('cancel_reason')->nullable();
            $table->text('note')->nullable();

            $table->timestamps();

            $table->index(['branch_id', 'status'], 'inv_branch_status_idx');
            $table->index(['branch_id', 'pos_open_date'], 'inv_branch_pos_open_date_idx');
            $table->index(['dining_session_id'], 'inv_session_idx');
            $table->index(['customer_id'], 'inv_customer_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
