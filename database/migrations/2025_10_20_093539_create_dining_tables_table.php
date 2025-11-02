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
        Schema::create('dining_tables', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->string('label');
            $table->enum('status', ['available', 'occupied', 'oncleaning', 'archived'])->default('available');
            $table->enum('shape', ['circle', 'rectangle'])->default('rectangle');
            $table->unsignedSmallInteger('x')->default(0);
            $table->unsignedSmallInteger('y')->default(0);
            $table->unsignedTinyInteger('h')->default(2);
            $table->unsignedTinyInteger('w')->default(2);
            $table->integer('capacity')->default(2);
            $table->string('color')->nullable();
            $table->string('qr_code')->nullable()->unique('qr_code');

            $table->index('tenant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dining_tables');
    }
};
