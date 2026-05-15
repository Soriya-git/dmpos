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
        Schema::create('bom_lines', function (Blueprint $table) {
            $table->id();

            $table->foreignId('bom_header_id')->constrained()->cascadeOnDelete();
            $table->foreignId('component_item_id')->constrained('items')->restrictOnDelete();
            $table->foreignId('unit_id')->constrained()->restrictOnDelete();

            $table->decimal('quantity', 12, 4)->default(1);
            $table->decimal('wastage_percent', 8, 4)->default(0);
            $table->decimal('estimated_cost', 12, 4)->default(0);

            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['bom_header_id', 'component_item_id'], 'bom_line_header_component_idx');
        });

        Schema::table('bom_headers', function (Blueprint $table) {
            $table->foreign('output_item_id', 'bom_headers_output_item_fk')
                ->references('id')
                ->on('items')
                ->restrictOnDelete();
        });

        Schema::table('menus', function (Blueprint $table) {
            $table->foreign('item_id', 'menus_item_fk')
                ->references('id')
                ->on('items')
                ->restrictOnDelete();

            $table->foreign('bom_header_id', 'menus_bom_header_fk')
                ->references('id')
                ->on('bom_headers')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->dropForeign('menus_bom_header_fk');
            $table->dropForeign('menus_item_fk');
        });

        Schema::table('bom_headers', function (Blueprint $table) {
            $table->dropForeign('bom_headers_output_item_fk');
        });

        Schema::dropIfExists('bom_lines');
    }
};
