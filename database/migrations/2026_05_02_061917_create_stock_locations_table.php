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
        Schema::create('stock_locations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();

            $table->string('name');
            $table->string('code')->nullable();

            $table->enum('location_type', [
                'inbound_staging',
                'putaway',
                'outbound_staging',
                'scrap',
                'damage',
                'obsolete',
                'customer_stock',
                'general',
            ])->default('putaway');

            // If false, stock here is not counted as saleable stock.
            $table->boolean('is_saleable')->default(true);

            $table->text('description')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['warehouse_id', 'location_type'], 'sl_warehouse_type_idx');
            $table->index(['branch_id', 'is_saleable'], 'sl_branch_saleable_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_locations');
    }
};
