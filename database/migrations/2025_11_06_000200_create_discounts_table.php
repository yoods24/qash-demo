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
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants');
            $table->string('name');
            $table->enum('discount_type', ['flat', 'percent']);
            $table->decimal('value', 10, 2);
            $table->enum('applicable_for', ['all', 'specific'])->default('all');
            $table->json('products')->nullable();
            $table->date('valid_from');
            $table->date('valid_till');
            $table->json('days');
            $table->enum('quantity_type', ['unlimited', 'decrement'])->default('unlimited');
            $table->integer('quantity')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'valid_from']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};
