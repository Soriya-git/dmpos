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
        Schema::create('kitchen_ticket_lines', function (Blueprint $table) {
            $table->id();

            $table->foreignId('kitchen_ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_line_id')->constrained()->cascadeOnDelete();
            $table->foreignId('menu_id')->constrained()->restrictOnDelete();

            $table->string('menu_name_snapshot');
            $table->decimal('quantity', 12, 4)->default(1);

            $table->enum('status', [
                'pending',
                'preparing',
                'ready',
                'served',
                'cancelled',
            ])->default('pending');

            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['kitchen_ticket_id', 'status'], 'ktl_ticket_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kitchen_ticket_lines');
    }
};
