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
        Schema::create('units', function (Blueprint $table) {
            $table->id();

            $table->string('name'); // Kilogram, Gram, Bottle, Can, Plate
            $table->string('code')->unique(); // KG, G, BTL, CAN, PLT
            $table->enum('category', [
                'count',
                'weight',
                'volume',
                'package',
                'service',
                'other',
            ])->default('count');
            $table->enum('unit_type', [
                'reference',
                'smaller_than_reference',
                'bigger_than_reference',
                'package',
            ])->default('reference');
            $table->foreignId('base_unit_id')
                ->nullable()
                ->constrained('units')
                ->nullOnDelete();
            $table->decimal('base_quantity', 18, 6)->default(1);
            $table->text('description')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['category', 'is_active'], 'units_category_active_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
