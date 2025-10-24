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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();

            // Multitenancy key
            $table->string('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();

            // Who and which shift (captured at the time for historical accuracy)
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('shift_id')->nullable()->constrained('shifts')->nullOnDelete();

            // The date being attended (business day)
            $table->date('work_date');

            // Clocking info
            $table->timestamp('clock_in_at')->nullable();
            $table->timestamp('clock_out_at')->nullable();

            // Aggregates (seconds). Can be recalculated from events/breaks.
            $table->unsignedInteger('break_seconds')->default(0);
            $table->unsignedInteger('production_seconds')->default(0);
            $table->integer('overtime_seconds')->default(0); // signed to allow negative for undertime

            // Status flags
            $table->enum('status', ['present', 'absent', 'half_day', 'holiday', 'leave'])
                ->default('present');
            $table->boolean('is_late')->default(false);

            // How the record was captured
            $table->enum('method', ['manual', 'geo', 'face'])->nullable();

            // Optional geolocation/device data
            $table->decimal('clock_in_lat', 10, 7)->nullable();
            $table->decimal('clock_in_lng', 10, 7)->nullable();
            $table->decimal('clock_out_lat', 10, 7)->nullable();
            $table->decimal('clock_out_lng', 10, 7)->nullable();
            $table->string('clock_in_device')->nullable();
            $table->string('clock_out_device')->nullable();

            $table->json('meta')->nullable();

            $table->timestamps();

            $table->unique(['tenant_id', 'user_id', 'work_date']);
            $table->index(['tenant_id', 'work_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};

