<?php

use App\Models\Category;
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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreignIdFor(Category::class)->constrained()->cascadeOnDelete();
            $table->string('product_image')->nullable();
            $table->string('name');
            $table->string('alternate_name')->nullable();
            $table->string('description');
            $table->decimal('price', 10, 2);
            $table->decimal('goods_price', 10, 2)->nullable();
            // Estimated preparation time in seconds (nullable = unknown)
            $table->integer('estimated_seconds')->nullable();
            $table->boolean('featured')->default(0)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
