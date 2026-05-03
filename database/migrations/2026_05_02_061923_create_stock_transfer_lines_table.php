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
        Schema::create('stock_transfer_lines', function (Blueprint $table) {
            $table->id();

            $table->foreignId('stock_transfer_id')->constrained()->cascadeOnDelete();

            $table->foreignId('item_id')->constrained()->restrictOnDelete();
            $table->foreignId('unit_id')->constrained()->restrictOnDelete();

            $table->decimal('quantity_requested', 14, 4)->default(0);
            $table->decimal('quantity_dispatched', 14, 4)->default(0);
            $table->decimal('quantity_received', 14, 4)->default(0);

            $table->decimal('unit_cost', 14, 4)->default(0);
            $table->decimal('total_cost', 14, 4)->default(0);

            $table->enum('status', [
                'open',
                'dispatched',
                'received',
                'cancelled',
            ])->default('open');

            $table->text('note')->nullable();

            $table->timestamps();

            $table->index(['stock_transfer_id', 'status'], 'stl_transfer_status_idx');
            $table->index(['item_id'], 'stl_item_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_transfer_lines');
    }
};
