<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_details', function (Blueprint $table) {
            $table->unsignedBigInteger('dining_table_id')->nullable()->after('gender');
            $table->foreign('dining_table_id')->references('id')->on('dining_tables')->nullOnDelete();
            $table->index(['tenant_id', 'dining_table_id']);
        });
    }

    public function down(): void
    {
        Schema::table('customer_details', function (Blueprint $table) {
            $table->dropForeign(['dining_table_id']);
            $table->dropIndex(['tenant_id', 'dining_table_id']);
            $table->dropColumn('dining_table_id');
        });
    }
};

