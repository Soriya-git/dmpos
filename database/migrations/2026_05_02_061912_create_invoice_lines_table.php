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
        Schema::create('invoice_lines', function (Blueprint $table) {
            $table->id();

            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();

            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_line_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('menu_id')->nullable()->constrained()->nullOnDelete();

            $table->string('menu_name_snapshot');
            $table->string('menu_type_snapshot')->default('product');

            $table->decimal('quantity', 12, 4)->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);

            // Tax snapshot
            $table->unsignedBigInteger('tax_id')->nullable();
            $table->string('tax_name_snapshot')->nullable();
            $table->decimal('tax_rate_snapshot', 8, 4)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);

            $table->decimal('line_subtotal', 12, 2)->default(0);
            $table->decimal('line_total', 12, 2)->default(0);

            $table->text('note')->nullable();

            $table->timestamps();

            $table->index(['invoice_id'], 'inv_lines_invoice_idx');
            $table->index(['order_line_id'], 'inv_lines_order_line_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_lines');
    }
};
