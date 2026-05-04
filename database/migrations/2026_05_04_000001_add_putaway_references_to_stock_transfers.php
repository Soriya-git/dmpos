<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_transfers', function (Blueprint $table) {
            $table->foreignId('goods_receipt_id')
                ->nullable()
                ->after('company_id')
                ->constrained('goods_receipts')
                ->nullOnDelete();
        });

        Schema::table('stock_transfer_lines', function (Blueprint $table) {
            $table->foreignId('to_location_id')
                ->nullable()
                ->after('unit_id')
                ->constrained('stock_locations')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('stock_transfer_lines', function (Blueprint $table) {
            $table->dropConstrainedForeignId('to_location_id');
        });

        Schema::table('stock_transfers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('goods_receipt_id');
        });
    }
};
