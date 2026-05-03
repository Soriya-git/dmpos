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
        Schema::create('pos_sessions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pos_terminal_id')->constrained()->cascadeOnDelete();

            $table->string('session_no')->unique();

            $table->enum('status', [
                'open',
                'closed',
                'cancelled',
            ])->default('open');

            $table->decimal('opening_cash_usd', 14, 2)->default(0);
            $table->decimal('opening_cash_khr', 14, 2)->default(0);

            $table->decimal('expected_cash_usd', 14, 2)->default(0);
            $table->decimal('expected_cash_khr', 14, 2)->default(0);

            $table->decimal('actual_cash_usd', 14, 2)->default(0);
            $table->decimal('actual_cash_khr', 14, 2)->default(0);

            $table->decimal('cash_variance_usd', 14, 2)->default(0);
            $table->decimal('cash_variance_khr', 14, 2)->default(0);

            $table->decimal('total_sales_usd', 14, 2)->default(0);
            $table->decimal('total_sales_khr', 14, 2)->default(0);

            $table->decimal('total_cash_usd', 14, 2)->default(0);
            $table->decimal('total_cash_khr', 14, 2)->default(0);

            $table->decimal('total_ebanking_usd', 14, 2)->default(0);
            $table->decimal('total_ebanking_khr', 14, 2)->default(0);

            $table->decimal('total_pay_later_usd', 14, 2)->default(0);
            $table->decimal('total_pay_later_khr', 14, 2)->default(0);

            $table->dateTime('opened_at')->nullable();
            $table->dateTime('closed_at')->nullable();

            $table->foreignId('opened_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();

            $table->text('opening_note')->nullable();
            $table->text('closing_note')->nullable();
            $table->text('cancel_reason')->nullable();

            $table->timestamps();

            $table->index(['branch_id', 'status'], 'pos_sessions_branch_status_idx');
            $table->index(['pos_terminal_id', 'status'], 'pos_sessions_terminal_status_idx');
            $table->index(['opened_by', 'status'], 'pos_sessions_user_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_sessions');
    }
};
