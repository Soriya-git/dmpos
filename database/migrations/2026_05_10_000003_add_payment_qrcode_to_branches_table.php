<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('branches', 'payment_qrcode')) {
            Schema::table('branches', function (Blueprint $table) {
                $table->string('payment_qrcode')->nullable()->after('logo');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('branches', 'payment_qrcode')) {
            Schema::table('branches', function (Blueprint $table) {
                $table->dropColumn('payment_qrcode');
            });
        }
    }
};
