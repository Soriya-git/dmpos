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
        Schema::create('resource_bookings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('dining_resource_id')->constrained()->cascadeOnDelete();

            $table->dateTime('booking_start');
            $table->dateTime('booking_end');

            $table->unsignedInteger('guest_count')->nullable();

            $table->enum('status', [
                'pending',
                'confirmed',
                'arrived',
                'converted',
                'cancelled',
                'no_show',
            ])->default('pending');

            $table->text('note')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(
                ['dining_resource_id', 'booking_start', 'booking_end'],
                'rb_resource_time_idx'
            );
            $table->index(['branch_id', 'status'], 'rb_branch_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resource_bookings');
    }
};
