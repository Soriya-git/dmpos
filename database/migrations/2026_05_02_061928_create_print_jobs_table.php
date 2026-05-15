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
        Schema::create('print_jobs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();

            $table->foreignId('printer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('print_template_id')->nullable()->constrained()->nullOnDelete();

            $table->string('job_no')->unique();

            $table->enum('job_type', [
                'receipt',
                'invoice',
                'kitchen_ticket',
                'stock_ticket',
                'bar_ticket',
                'cancel_slip',
                'payment_receipt',
            ])->default('receipt');

            $table->enum('status', [
                'pending',
                'printed',
                'failed',
                'cancelled',
            ])->default('pending');

            // Polymorphic reference:
            // invoice, kitchen_ticket, payment, etc.
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reference_no')->nullable();

            $table->unsignedInteger('print_count')->default(0);

            $table->dateTime('printed_at')->nullable();
            $table->foreignId('printed_by')->nullable()->constrained('users')->nullOnDelete();

            $table->text('error_message')->nullable();
            $table->json('payload')->nullable();

            $table->timestamps();

            $table->index(['branch_id', 'status'], 'pj_branch_status_idx');
            $table->index(['reference_type', 'reference_id'], 'pj_reference_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('print_jobs');
    }
};
