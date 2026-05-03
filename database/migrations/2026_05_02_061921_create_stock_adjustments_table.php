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
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();

            $table->foreignId('warehouse_id')->constrained()->restrictOnDelete();
            $table->foreignId('stock_location_id')->constrained()->restrictOnDelete();

            $table->string('adjustment_no')->unique();

            $table->enum('adjustment_type', [
                'adjustment_in',
                'adjustment_out',
                'pos_settlement',
                'scrap',
                'damage',
                'obsolete',
                'write_off',
            ]);

            $table->enum('status', [
                'draft',
                'confirmed',
                'cancelled',
            ])->default('draft');

            $table->dateTime('adjustment_date')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();

            $table->dateTime('confirmed_at')->nullable();
            $table->dateTime('cancelled_at')->nullable();

            $table->text('cancel_reason')->nullable();
            $table->text('note')->nullable();

            $table->timestamps();

            $table->index(['branch_id', 'status'], 'sa_branch_status_idx');
            $table->index(['adjustment_type'], 'sa_type_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }
};
