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
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')->constrained()->cascadeOnDelete();

            $table->string('from_currency', 3)->default('USD');
            $table->string('to_currency', 3)->default('KHR');

            $table->decimal('rate', 12, 4)->default(4100); // 1 USD = 4100 KHR
            $table->date('effective_date');

            $table->boolean('is_active')->default(true);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['company_id', 'from_currency', 'to_currency'], 'ex_company_currency_idx');
            $table->index(['effective_date', 'is_active'], 'ex_date_active_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};
