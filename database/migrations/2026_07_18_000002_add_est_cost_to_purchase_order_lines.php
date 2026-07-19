<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_order_lines', function (Blueprint $table) {
            $table->decimal('est_cost', 14, 4)->default(0)->after('quantity_remaining');
        });

        DB::table('purchase_order_lines')->update([
            'est_cost' => DB::raw('unit_cost'),
            'unit_cost' => 0,
        ]);
    }

    public function down(): void
    {
        DB::table('purchase_order_lines')->update([
            'unit_cost' => DB::raw('est_cost'),
        ]);

        Schema::table('purchase_order_lines', function (Blueprint $table) {
            $table->dropColumn('est_cost');
        });
    }
};
