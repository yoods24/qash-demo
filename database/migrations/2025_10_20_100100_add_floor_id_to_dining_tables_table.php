<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dining_tables', function (Blueprint $table) {
            $table->unsignedBigInteger('floor_id')->nullable()->after('tenant_id');
            $table->foreign('floor_id')->references('id')->on('floors')->cascadeOnDelete();
            $table->index(['tenant_id', 'floor_id']);
        });
    }

    public function down(): void
    {
        Schema::table('dining_tables', function (Blueprint $table) {
            $table->dropForeign(['floor_id']);
            $table->dropColumn('floor_id');
        });
    }
};

