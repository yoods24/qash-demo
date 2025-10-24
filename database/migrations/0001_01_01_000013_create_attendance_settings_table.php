<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_settings', function (Blueprint $table) {
            $table->id();

            $table->string('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();

            // Feature toggles
            $table->boolean('face_recognition_enabled')->default(false);
            $table->enum('default_method', ['manual', 'geo', 'face'])->default('geo');

            // Optional geofence configuration payload (e.g., {"lat":..,"lng":..,"radius":..})
            $table->json('geofence')->nullable();

            $table->timestamps();
            // attendance settings dbs
            $table->boolean('default_combined')->default(false);
            $table->enum('apply_face_to', ['all', 'per_user'])->default('per_user');
            $table->json('meta')->nullable();

            $table->unique('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_settings');
    }
};

