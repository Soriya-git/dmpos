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
        Schema::create('session_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('dining_session_id')->constrained()->cascadeOnDelete();

            $table->string('action');
            // opened, closed, moved, merged, split, cancelled, pay_later

            $table->foreignId('from_resource_id')
                ->nullable()
                ->constrained('dining_resources')
                ->nullOnDelete();

            $table->foreignId('to_resource_id')
                ->nullable()
                ->constrained('dining_resources')
                ->nullOnDelete();

            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();

            $table->json('payload')->nullable();
            $table->text('note')->nullable();

            $table->timestamps();

            $table->index(['branch_id', 'action'], 'session_logs_branch_action_idx');
            $table->index(['dining_session_id'], 'session_logs_session_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('session_logs');
    }
};
