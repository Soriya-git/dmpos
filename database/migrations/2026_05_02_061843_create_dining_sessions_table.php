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
        Schema::create('dining_sessions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pos_terminal_id')->nullable()->constrained()->nullOnDelete();
            $table->date('pos_open_date')->nullable();

            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('dining_resource_id')->constrained()->cascadeOnDelete();
            $table->foreignId('resource_booking_id')->nullable()->constrained()->nullOnDelete();

            $table->string('session_no')->unique();

            $table->unsignedInteger('guest_count')->nullable();

            $table->enum('status', [
                'open',
                'invoiced',
                'partially_paid',
                'paid',
                'pay_later',
                'closed',
                'cancelled',
            ])->default('open');

            $table->dateTime('opened_at')->nullable();
            $table->dateTime('closed_at')->nullable();

            $table->foreignId('opened_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();

            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['branch_id', 'status']);
            $table->index(['dining_resource_id', 'status']);
            $table->index(['branch_id', 'pos_open_date'], 'ds_branch_pos_open_date_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dining_sessions');
    }
};
