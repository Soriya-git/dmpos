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
        Schema::create('printers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();

            $table->string('name'); // Cashier Printer, Kitchen Printer
            $table->string('code')->nullable();

            $table->enum('printer_type', [
                'receipt',
                'invoice',
                'kitchen',
                'general',
            ])->default('receipt');

            $table->enum('connection_type', [
                'native_app',
                'usb_bridge',
                'network',
                'browser_dialog',
            ])->default('browser_dialog');

            $table->string('ip_address')->nullable();
            $table->unsignedInteger('port')->nullable(); // usually 9100
            $table->string('paper_size')->default('80mm');

            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);

            $table->json('settings')->nullable();

            $table->timestamps();

            $table->index(['branch_id', 'printer_type'], 'printer_branch_type_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('printers');
    }
};
