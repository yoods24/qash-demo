<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Allow storing the combined label directly
        DB::statement("ALTER TABLE attendance_settings MODIFY default_method ENUM('manual','geo','face','face+geo') NOT NULL DEFAULT 'geo'");

        // Normalize existing combined rows to the new label
        DB::table('attendance_settings')
            ->where('default_combined', true)
            ->update(['default_method' => 'face+geo']);
    }

    public function down(): void
    {
        // Revert to the old enum definition
        DB::statement("ALTER TABLE attendance_settings MODIFY default_method ENUM('manual','geo','face') NOT NULL DEFAULT 'geo'");

        // Existing 'face+geo' values fallback to 'geo'
        DB::table('attendance_settings')
            ->where('default_method', 'face+geo')
            ->update(['default_method' => 'geo']);
    }
};
