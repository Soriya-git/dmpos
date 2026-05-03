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
        Schema::create('stock_adjustment_lines', function (Blueprint $table) {
            $table->id();

            $table->foreignId('stock_adjustment_id')->constrained()->cascadeOnDelete();

            $table->foreignId('item_id')->constrained()->restrictOnDelete();
            $table->foreignId('unit_id')->constrained()->restrictOnDelete();

            $table->decimal('system_quantity', 14, 4)->default(0);
            $table->decimal('adjusted_quantity', 14, 4)->default(0);
            $table->decimal('difference_quantity', 14, 4)->default(0);

            $table->decimal('unit_cost', 14, 4)->default(0);
            $table->decimal('total_cost', 14, 4)->default(0);

            // For POS settlement link
            $table->unsignedBigInteger('menu_id')->nullable();
            $table->unsignedBigInteger('bom_header_id')->nullable();

            $table->text('reason')->nullable();
            $table->text('note')->nullable();

            $table->timestamps();

            $table->index(['stock_adjustment_id'], 'sal_adjustment_idx');
            $table->index(['item_id'], 'sal_item_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_adjustment_lines');
    }
};
