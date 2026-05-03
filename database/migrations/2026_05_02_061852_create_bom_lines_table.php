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
            $table->foreignId('item_id')->constrained()->restrictOnDelete();
            $table->foreignId('unit_id')->constrained()->restrictOnDelete();

            $table->decimal('quantity', 12, 4)->default(1);
            $table->decimal('wastage_percent', 8, 4)->default(0);
            $table->decimal('estimated_cost', 12, 4)->default(0);

            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['bom_header_id', 'item_id'], 'bom_line_header_item_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bom_lines');
    }
};
