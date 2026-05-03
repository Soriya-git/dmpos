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
        Schema::table('menus', function (Blueprint $table) {
            $table->foreign('tax_id')
                ->references('id')
                ->on('taxes')
                ->nullOnDelete();
        });

        Schema::table('order_lines', function (Blueprint $table) {
            $table->foreign('tax_id')
                ->references('id')
                ->on('taxes')
                ->nullOnDelete();
        });

        Schema::table('invoice_lines', function (Blueprint $table) {
            $table->foreign('tax_id')
                ->references('id')
                ->on('taxes')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('invoice_lines', function (Blueprint $table) {
            $table->dropForeign(['tax_id']);
        });

        Schema::table('order_lines', function (Blueprint $table) {
            $table->dropForeign(['tax_id']);
        });

        Schema::table('menus', function (Blueprint $table) {
            $table->dropForeign(['tax_id']);
        });
    }
};
