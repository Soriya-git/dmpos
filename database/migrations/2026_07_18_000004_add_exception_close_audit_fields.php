<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->text('close_reason')->nullable()->after('note');
            $table->dateTime('closed_at')->nullable()->after('cancelled_at');
            $table->foreignId('closed_by')->nullable()->after('cancelled_by')->constrained('users')->nullOnDelete();
        });

        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->text('putaway_cancel_reason')->nullable()->after('cancel_reason');
            $table->dateTime('putaway_cancelled_at')->nullable()->after('received_at');
            $table->foreignId('putaway_cancelled_by')->nullable()->after('cancelled_by')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('putaway_cancelled_by');
            $table->dropColumn(['putaway_cancelled_at', 'putaway_cancel_reason']);
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('closed_by');
            $table->dropColumn(['closed_at', 'close_reason']);
        });
    }
};
