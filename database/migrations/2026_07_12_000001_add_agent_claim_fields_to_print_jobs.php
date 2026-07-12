<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('print_jobs', function (Blueprint $table) {
            $table->uuid('claim_token')->nullable()->after('status');
            $table->dateTime('claimed_at')->nullable()->after('claim_token');
            $table->index(['branch_id', 'status', 'claimed_at'], 'pj_agent_queue_idx');
        });
    }

    public function down(): void
    {
        Schema::table('print_jobs', function (Blueprint $table) {
            $table->dropIndex('pj_agent_queue_idx');
            $table->dropColumn(['claim_token', 'claimed_at']);
        });
    }
};
