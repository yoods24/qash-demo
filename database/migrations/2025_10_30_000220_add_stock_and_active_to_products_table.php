<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('active')->default(true)->after('featured');
            $table->unsignedInteger('stock_qty')->default(0)->after('active');
            $table->index(['tenant_id', 'active']);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'active']);
            $table->dropColumn(['active', 'stock_qty']);
        });
    }
};

