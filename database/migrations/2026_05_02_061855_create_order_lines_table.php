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
        Schema::create('order_lines', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('menu_id')->constrained()->restrictOnDelete();

            $table->string('menu_name_snapshot');
            $table->string('menu_type_snapshot')->default('product');

            $table->decimal('quantity', 12, 4)->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);

            $table->unsignedBigInteger('tax_id')->nullable();
            $table->decimal('tax_rate_snapshot', 8, 4)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);

            $table->decimal('line_subtotal', 12, 2)->default(0);
            $table->decimal('line_total', 12, 2)->default(0);

            $table->enum('status', [
                'ordered',
                'preparing',
                'ready',
                'served',
                'cancelled',
            ])->default('ordered');

            $table->text('note')->nullable();

            $table->timestamps();

            $table->index(['order_id', 'status'], 'order_lines_order_status_idx');
            $table->index(['menu_id'], 'order_lines_menu_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_lines');
    }
};
