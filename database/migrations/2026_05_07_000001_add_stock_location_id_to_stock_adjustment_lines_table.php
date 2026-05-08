<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_adjustment_lines', function (Blueprint $table) {
            $table->foreignId('stock_location_id')
                ->nullable()
                ->after('unit_id')
                ->constrained('stock_locations')
                ->nullOnDelete();

            $table->index(['stock_location_id'], 'sal_location_idx');
        });
    }

    public function down(): void
    {
        Schema::table('stock_adjustment_lines', function (Blueprint $table) {
            $table->dropIndex('sal_location_idx');
            $table->dropConstrainedForeignId('stock_location_id');
        });
    }
};
