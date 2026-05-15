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
        Schema::table('printers', function (Blueprint $table) {
            $table->enum('printer_role', [
                'cashier',
                'kitchen',
                'stock',
                'bar',
                'general',
            ])->default('general')->after('printer_type');
            $table->string('network_protocol')->default('raw_tcp')->after('connection_type');
            $table->string('host_name')->nullable()->after('ip_address');
            $table->unsignedInteger('timeout_ms')->default(5000)->after('port');

            $table->index(['branch_id', 'printer_role'], 'printer_branch_role_idx');
        });

        Schema::table('items', function (Blueprint $table) {
            $table->foreignId('printer_id')
                ->nullable()
                ->after('unit_id')
                ->constrained()
                ->nullOnDelete();
            $table->enum('print_route', [
                'none',
                'stock',
                'kitchen',
                'bar',
                'cashier',
                'custom',
            ])->default('none')->after('printer_id');

            $table->index(['printer_id'], 'items_printer_idx');
            $table->index(['print_route'], 'items_print_route_idx');
        });

        Schema::table('menus', function (Blueprint $table) {
            $table->foreignId('printer_id')
                ->nullable()
                ->after('bom_header_id')
                ->constrained()
                ->nullOnDelete();
            $table->enum('print_route', [
                'none',
                'stock',
                'kitchen',
                'bar',
                'cashier',
                'custom',
            ])->default('kitchen')->after('printer_id');

            $table->index(['printer_id'], 'menus_printer_idx');
            $table->index(['print_route'], 'menus_print_route_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->dropForeign(['printer_id']);
            $table->dropIndex('menus_printer_idx');
            $table->dropIndex('menus_print_route_idx');
            $table->dropColumn(['printer_id', 'print_route']);
        });

        Schema::table('items', function (Blueprint $table) {
            $table->dropForeign(['printer_id']);
            $table->dropIndex('items_printer_idx');
            $table->dropIndex('items_print_route_idx');
            $table->dropColumn(['printer_id', 'print_route']);
        });

        Schema::table('printers', function (Blueprint $table) {
            $table->dropIndex('printer_branch_role_idx');
            $table->dropColumn([
                'printer_role',
                'network_protocol',
                'host_name',
                'timeout_ms',
            ]);
        });
    }
};
