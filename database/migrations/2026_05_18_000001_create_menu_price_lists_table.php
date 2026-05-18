<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_price_lists', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();

            $table->string('name');
            $table->string('code');
            $table->text('description')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique(['company_id', 'branch_id', 'code'], 'menu_price_lists_scope_code_unique');
            $table->index(['company_id', 'branch_id', 'is_default'], 'menu_price_lists_default_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_price_lists');
    }
};
