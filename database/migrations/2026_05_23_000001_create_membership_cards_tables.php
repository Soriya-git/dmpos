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
        Schema::create('membership_cards', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();

            $table->string('card_no')->unique();
            $table->string('card_name')->nullable();

            $table->enum('status', [
                'active',
                'inactive',
                'blocked',
                'expired',
                'cancelled',
            ])->default('active');

            $table->date('issued_date')->nullable();
            $table->date('expired_date')->nullable();

            $table->text('remark')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['company_id', 'branch_id'], 'member_cards_company_branch_idx');
            $table->index(['customer_id', 'status'], 'member_cards_customer_status_idx');
        });

        Schema::create('membership_card_balances', function (Blueprint $table) {
            $table->id();

            $table->foreignId('membership_card_id')->constrained()->cascadeOnDelete();

            $table->string('currency', 3);
            $table->decimal('balance', 16, 2)->default(0);

            $table->timestamps();

            $table->unique(['membership_card_id', 'currency'], 'member_card_balances_card_currency_unique');
            $table->index(['currency'], 'member_card_balances_currency_idx');
        });

        Schema::create('membership_card_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('membership_card_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();

            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();

            $table->string('transaction_no')->unique();
            $table->enum('transaction_type', [
                'recharge',
                'payment',
                'promotion',
                'adjustment',
                'refund',
                'void',
            ]);

            $table->enum('direction', [
                'credit',
                'debit',
            ]);

            $table->string('currency', 3)->default('USD');
            $table->decimal('amount', 16, 2)->default(0);
            $table->decimal('promotion_amount', 16, 2)->default(0);
            $table->string('promotion_name')->nullable();

            $table->decimal('balance_before', 16, 2)->default(0);
            $table->decimal('balance_after', 16, 2)->default(0);

            $table->decimal('exchange_rate_snapshot', 12, 4)->default(4100);
            $table->decimal('amount_usd_equivalent', 14, 2)->default(0);
            $table->decimal('amount_khr_equivalent', 16, 2)->default(0);

            $table->dateTime('transacted_at')->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();

            $table->json('payload')->nullable();
            $table->text('note')->nullable();

            $table->timestamps();

            $table->index(['company_id', 'branch_id'], 'member_card_tx_company_branch_idx');
            $table->index(['membership_card_id', 'created_at'], 'member_card_tx_card_created_idx');
            $table->index(['customer_id', 'created_at'], 'member_card_tx_customer_created_idx');
            $table->index(['invoice_id'], 'member_card_tx_invoice_idx');
            $table->index(['payment_id'], 'member_card_tx_payment_idx');
            $table->index(['transaction_type', 'direction'], 'member_card_tx_type_direction_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('membership_card_transactions');
        Schema::dropIfExists('membership_card_balances');
        Schema::dropIfExists('membership_cards');
    }
};
