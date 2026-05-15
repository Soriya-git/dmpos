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
        Schema::create('print_templates', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();

            $table->string('name'); // Main Receipt Template
            $table->string('code')->nullable();

            $table->enum('template_type', [
                'receipt',
                'invoice',
                'kitchen_ticket',
                'stock_ticket',
                'bar_ticket',
                'cancel_slip',
                'payment_receipt',
            ])->default('receipt');

            $table->string('paper_size')->default('80mm');

            $table->string('logo')->nullable();

            // Store layout settings:
            // show_customer, show_room, show_qr, header_position, footer_text, etc.
            $table->json('layout_config')->nullable();

            // Optional raw Blade/template HTML if you want customizable templates later.
            $table->longText('template_html')->nullable();

            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['company_id', 'branch_id'], 'pt_company_branch_idx');
            $table->index(['template_type'], 'pt_template_type_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('print_templates');
    }
};
