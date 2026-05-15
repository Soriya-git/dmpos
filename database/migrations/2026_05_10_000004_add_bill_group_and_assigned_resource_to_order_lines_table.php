<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_lines', function (Blueprint $table) {
            if (! Schema::hasColumn('order_lines', 'bill_group')) {
                $table->string('bill_group')->nullable()->after('line_total');
                $table->index(['bill_group'], 'order_lines_bill_group_idx');
            }

            if (! Schema::hasColumn('order_lines', 'assigned_dining_resource_id')) {
                $table->foreignId('assigned_dining_resource_id')
                    ->nullable()
                    ->after('bill_group')
                    ->constrained('dining_resources')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_lines', function (Blueprint $table) {
            if (Schema::hasColumn('order_lines', 'assigned_dining_resource_id')) {
                $table->dropConstrainedForeignId('assigned_dining_resource_id');
            }

            if (Schema::hasColumn('order_lines', 'bill_group')) {
                $table->dropIndex('order_lines_bill_group_idx');
                $table->dropColumn('bill_group');
            }
        });
    }
};
