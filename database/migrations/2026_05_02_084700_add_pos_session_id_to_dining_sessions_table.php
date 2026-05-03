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
        Schema::table('dining_sessions', function (Blueprint $table) {
            $table->foreignId('pos_session_id')
                ->nullable()
                ->after('pos_terminal_id')
                ->constrained('pos_sessions')
                ->nullOnDelete();

            $table->index(['pos_session_id', 'status'], 'ds_pos_session_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dining_sessions', function (Blueprint $table) {
            //
        });
    }
};
