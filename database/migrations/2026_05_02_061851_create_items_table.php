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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
