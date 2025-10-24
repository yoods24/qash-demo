<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('floors', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->string('area_type')->nullable(); // e.g., indoor/outdoor or any string
            $table->unsignedInteger('order')->default(0);
            $table->index(['tenant_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('floors');
    }
};

