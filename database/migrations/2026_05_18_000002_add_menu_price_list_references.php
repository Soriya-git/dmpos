<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_prices', function (Blueprint $table) {
            $table->foreignId('menu_price_list_id')
                ->nullable()
                ->after('branch_id')
                ->constrained('menu_price_lists')
                ->nullOnDelete();

            $table->index(['menu_price_list_id', 'menu_id'], 'menu_prices_list_menu_idx');
        });

        Schema::table('dining_sessions', function (Blueprint $table) {
            $table->foreignId('menu_price_list_id')
                ->nullable()
                ->after('resource_booking_id')
                ->constrained('menu_price_lists')
                ->nullOnDelete();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('menu_price_list_id')
                ->nullable()
                ->after('dining_session_id')
                ->constrained('menu_price_lists')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('menu_price_list_id');
        });

        Schema::table('dining_sessions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('menu_price_list_id');
        });

        Schema::table('menu_prices', function (Blueprint $table) {
            $table->dropIndex('menu_prices_list_menu_idx');
            $table->dropConstrainedForeignId('menu_price_list_id');
        });
    }
};
