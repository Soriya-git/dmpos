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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('dining_session_id')->constrained()->cascadeOnDelete();

            $table->string('order_no')->unique();

            $table->enum('status', [
                'draft',
                'sent_to_kitchen',
                'preparing',
                'ready',
                'served',
                'cancelled',
            ])->default('draft');

            $table->dateTime('sent_to_kitchen_at')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();

            $table->text('cancel_reason')->nullable();
            $table->text('note')->nullable();

            $table->timestamps();

            $table->index(['branch_id', 'status'], 'orders_branch_status_idx');
            $table->index(['dining_session_id', 'status'], 'orders_session_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
