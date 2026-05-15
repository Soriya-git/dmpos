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
            DB::statement("ALTER TABLE order_lines MODIFY status ENUM('ordered','preparing','ready','served','cancelled','returned') DEFAULT 'ordered'");
        }

        Schema::table('invoice_lines', function (Blueprint $table) {
            if (! Schema::hasColumn('invoice_lines', 'status')) {
                $table->string('status')->default('ordered')->after('line_total');
                $table->index(['status'], 'inv_lines_status_idx');
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoice_lines', function (Blueprint $table) {
            if (Schema::hasColumn('invoice_lines', 'status')) {
                $table->dropIndex('inv_lines_status_idx');
                $table->dropColumn('status');
            }
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE order_lines MODIFY status ENUM('ordered','preparing','ready','served','cancelled') DEFAULT 'ordered'");
        }
    }
};
