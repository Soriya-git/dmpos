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
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();

            $table->string('name'); // Cash USD, Cash KHR, E-Banking USD
            $table->string('code')->nullable();

            $table->enum('method_type', [
                'cash',
                'bank',
                'card',
                'qr',
                'other',
            ])->default('cash');

            $table->string('currency', 3)->default('USD'); // USD or KHR

            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['company_id', 'branch_id'], 'pm_company_branch_idx');
            $table->index(['currency', 'method_type'], 'pm_currency_type_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
