<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->unsignedBigInteger('product_id')->nullable()->index();
            $table->string('product_name');
            $table->decimal('unit_price', 10, 2);
            $table->integer('quantity');
            // Estimated preparation seconds per unit (copied from product at order time)
            $table->integer('estimate_seconds')->nullable();
            $table->json('options')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
