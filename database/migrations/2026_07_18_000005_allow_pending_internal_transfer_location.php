<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_transfers', function (Blueprint $table) {
            $table->foreignId('to_location_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Pending cross-warehouse transfers may not have a destination location.
    }
};
