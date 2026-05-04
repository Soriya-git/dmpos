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
        Schema::create('items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('unit_id')->constrained()->restrictOnDelete();

            $table->string('name');
            $table->string('code')->nullable();

            $table->enum('item_type', [
                'raw_material',
                'ingredient',
                'drink',
                'finished_product',
                'packaging',
                'service_material',
                'other',
            ])->default('raw_material');

            $table->decimal('cost', 12, 4)->default(0);
            $table->boolean('is_stockable')->default(true);
            $table->boolean('is_active')->default(true);

            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'branch_id'], 'items_company_branch_idx');
            $table->index(['item_type'], 'items_type_idx');
        });

        Schema::create('item_unit_conversions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();

            $table->foreignId('from_unit_id')->constrained('units')->restrictOnDelete();
            $table->foreignId('to_unit_id')->constrained('units')->restrictOnDelete();

            // Example: from_unit BOX, to_unit PCS, factor 12 means 1 BOX = 12 PCS.
            $table->decimal('factor', 18, 6);

            $table->boolean('is_purchase_default')->default(false);
            $table->boolean('is_sales_default')->default(false);
            $table->boolean('is_inventory_default')->default(false);
            $table->boolean('is_active')->default(true);

            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(
                ['item_id', 'from_unit_id', 'to_unit_id'],
                'item_unit_conversion_unique'
            );
            $table->index(['company_id', 'branch_id'], 'iuc_company_branch_idx');
            $table->index(['from_unit_id', 'to_unit_id'], 'iuc_from_to_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_unit_conversions');
        Schema::dropIfExists('items');
    }
};
