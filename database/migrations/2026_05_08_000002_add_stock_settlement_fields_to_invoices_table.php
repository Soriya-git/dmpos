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
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('stock_settlement_status', 30)->default('pending')->after('status');
            $table->decimal('stock_settled_quantity', 14, 4)->default(0)->after('stock_settlement_status');
            $table->dateTime('stock_settled_at')->nullable()->after('stock_settled_quantity');
            $table->foreignId('stock_settled_by')->nullable()->after('stock_settled_at')->constrained('users')->nullOnDelete();
            $table->dateTime('stock_rejected_at')->nullable()->after('stock_settled_by');
            $table->foreignId('stock_rejected_by')->nullable()->after('stock_rejected_at')->constrained('users')->nullOnDelete();
            $table->text('stock_settlement_note')->nullable()->after('stock_rejected_by');

            $table->index(['branch_id', 'stock_settlement_status'], 'inv_branch_stock_settlement_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex('inv_branch_stock_settlement_idx');
            $table->dropConstrainedForeignId('stock_settled_by');
            $table->dropConstrainedForeignId('stock_rejected_by');
            $table->dropColumn([
                'stock_settlement_status',
                'stock_settled_quantity',
                'stock_settled_at',
                'stock_rejected_at',
                'stock_settlement_note',
            ]);
        });
    }
};
