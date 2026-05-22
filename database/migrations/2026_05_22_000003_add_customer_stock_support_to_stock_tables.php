<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE stock_locations MODIFY location_type ENUM('inbound_staging','putaway','outbound_staging','scrap','damage','obsolete','customer_stock','general') DEFAULT 'putaway'");
            DB::statement("ALTER TABLE stock_transfers MODIFY transfer_type ENUM('internal_transfer','branch_transfer','scrap_transfer','damage_transfer','obsolete_transfer','customer_stock_keep') DEFAULT 'internal_transfer'");
            DB::statement("ALTER TABLE stock_movements MODIFY movement_type ENUM('purchase_receipt','adjustment_in','adjustment_out','pos_settlement','internal_transfer','scrap_transfer','damage_transfer','obsolete_transfer','customer_stock_keep','write_off')");
        }

        Schema::table('stock_transfers', function (Blueprint $table) {
            if (! Schema::hasColumn('stock_transfers', 'invoice_id')) {
                $table->foreignId('invoice_id')
                    ->nullable()
                    ->after('goods_receipt_id')
                    ->constrained()
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('stock_transfers', function (Blueprint $table) {
            if (Schema::hasColumn('stock_transfers', 'invoice_id')) {
                $table->dropConstrainedForeignId('invoice_id');
            }
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE stock_locations MODIFY location_type ENUM('inbound_staging','putaway','outbound_staging','scrap','damage','obsolete','general') DEFAULT 'putaway'");
            DB::statement("ALTER TABLE stock_transfers MODIFY transfer_type ENUM('internal_transfer','branch_transfer','scrap_transfer','damage_transfer','obsolete_transfer') DEFAULT 'internal_transfer'");
            DB::statement("ALTER TABLE stock_movements MODIFY movement_type ENUM('purchase_receipt','adjustment_in','adjustment_out','pos_settlement','internal_transfer','scrap_transfer','damage_transfer','obsolete_transfer','write_off')");
        }
    }
};
