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
        Schema::create('purchase_order_lines', function (Blueprint $table) {
            $table->id();

            $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();

            // Target branch that will receive/use this item
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();

            $table->foreignId('item_id')->constrained()->restrictOnDelete();
            $table->foreignId('unit_id')->constrained()->restrictOnDelete();

            $table->decimal('quantity_ordered', 14, 4)->default(0);
            $table->decimal('quantity_received', 14, 4)->default(0);
            $table->decimal('quantity_remaining', 14, 4)->default(0);

            $table->decimal('unit_cost', 14, 4)->default(0);
            $table->decimal('discount_amount', 14, 2)->default(0);
            $table->decimal('tax_amount', 14, 2)->default(0);
            $table->decimal('line_total', 14, 2)->default(0);

            $table->enum('status', [
                'open',
                'partially_received',
                'received',
                'cancelled',
            ])->default('open');

            $table->text('note')->nullable();

            $table->timestamps();

            $table->index(['purchase_order_id', 'status'], 'pol_po_status_idx');
            $table->index(['item_id'], 'pol_item_idx');
            $table->index(['branch_id'], 'pol_branch_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_order_lines');
    }
};
