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
        Schema::create('menus', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('menu_category_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('item_id')->nullable();
            $table->unsignedBigInteger('bom_header_id')->nullable();

            $table->string('name');
            $table->string('code')->nullable();

            $table->enum('menu_type', [
                'product',
                'service',
            ])->default('product');

            $table->decimal('base_price', 12, 2)->default(0);

            // Later this links to tax table from migration 6.
            $table->unsignedBigInteger('tax_id')->nullable();

            $table->string('image')->nullable();
            $table->text('description')->nullable();

            $table->boolean('is_available')->default(true);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['company_id', 'branch_id'], 'menus_company_branch_idx');
            $table->index(['menu_type'], 'menus_type_idx');
            $table->index(['is_available'], 'menus_available_idx');
            $table->index(['item_id'], 'menus_item_idx');
            $table->index(['bom_header_id'], 'menus_bom_header_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
