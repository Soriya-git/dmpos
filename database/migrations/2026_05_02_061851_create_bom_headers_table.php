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
        Schema::create('bom_headers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('menu_id')->constrained()->cascadeOnDelete();

            $table->string('bom_no')->unique();
            $table->string('name');

            $table->decimal('output_quantity', 12, 4)->default(1);

            $table->enum('status', [
                'draft',
                'active',
                'inactive',
            ])->default('draft');

            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();

            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['menu_id', 'status'], 'bom_menu_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bom_headers');
    }
};
