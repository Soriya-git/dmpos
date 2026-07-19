<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->decimal('est_cost', 14, 2)->nullable()->after('grand_total');
        });

        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->json('photos')->nullable()->after('note');
        });
    }

    public function down(): void
    {
        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->dropColumn('photos');
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn('est_cost');
        });
    }
};
