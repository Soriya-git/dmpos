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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();

            $table->foreignId('warehouse_id')->nullable()->constrained()->nullOnDelete();

            $table->foreignId('from_location_id')
                ->nullable()
                ->constrained('stock_locations')
                ->nullOnDelete();

            $table->foreignId('to_location_id')
                ->nullable()
                ->constrained('stock_locations')
                ->nullOnDelete();

            $table->foreignId('item_id')->constrained()->restrictOnDelete();
            $table->foreignId('unit_id')->constrained()->restrictOnDelete();

            $table->enum('movement_type', [
                'purchase_receipt',
                'adjustment_in',
                'adjustment_out',
                'pos_settlement',
                'internal_transfer',
                'scrap_transfer',
                'damage_transfer',
                'obsolete_transfer',
                'customer_stock_keep',
                'write_off',
            ]);

            $table->decimal('quantity', 14, 4);
            $table->decimal('unit_cost', 14, 4)->default(0);
            $table->decimal('total_cost', 14, 4)->default(0);

            // Polymorphic reference:
            // purchase_receipt, stock_adjustment, stock_transfer, etc.
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reference_no')->nullable();

            $table->dateTime('movement_date')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->text('note')->nullable();

            $table->timestamps();

            $table->index(['branch_id', 'movement_type'], 'sm_branch_type_idx');
            $table->index(['item_id', 'movement_date'], 'sm_item_date_idx');
            $table->index(['reference_type', 'reference_id'], 'sm_reference_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
