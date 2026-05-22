<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dining_sessions', function (Blueprint $table) {
            if (! Schema::hasColumn('dining_sessions', 'pos_open_date')) {
                $table->date('pos_open_date')->nullable()->after('pos_terminal_id');
                $table->index(['branch_id', 'pos_open_date'], 'ds_branch_pos_open_date_idx');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'pos_open_date')) {
                $table->date('pos_open_date')->nullable()->after('dining_session_id');
                $table->index(['branch_id', 'pos_open_date'], 'orders_branch_pos_open_date_idx');
            }
        });

        Schema::table('invoices', function (Blueprint $table) {
            if (! Schema::hasColumn('invoices', 'pos_open_date')) {
                $table->date('pos_open_date')->nullable()->after('pos_terminal_id');
                $table->index(['branch_id', 'pos_open_date'], 'inv_branch_pos_open_date_idx');
            }
        });
    }

    public function down(): void
    {
        // The base create-table migrations also define these columns, so this
        // guarded migration intentionally leaves rollback to those migrations.
    }
};
