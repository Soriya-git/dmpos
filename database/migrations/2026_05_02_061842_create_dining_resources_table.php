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
        Schema::create('dining_resources', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('dining_resource_type_id')->constrained()->cascadeOnDelete();

            $table->string('name'); // VIP Room 01, Table A1
            $table->string('code')->nullable();

            $table->unsignedInteger('capacity')->default(1);

            $table->enum('status', [
                'available',
                'booked',
                'occupied',
                'maintenance',
                'disabled',
            ])->default('available');

            $table->string('image')->nullable();
            $table->text('description')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['branch_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dining_resources');
    }
};
