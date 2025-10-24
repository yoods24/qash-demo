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
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();

            $table->string('name');
            // Shift timing (24h values stored as TIME)
            $table->time('start_time');
            $table->time('end_time');

            // Optional week off days (store as array of day numbers 1=Mon..7=Sun)
            $table->json('week_off_days')->nullable();

            // Day rules for weeks of month, e.g.
            // {\"1\":{\"weeks\":[\"all\"]}, \"2\":{\"weeks\":[1,3]}, ...}
            $table->json('day_rules')->nullable();

            // Break timings payload
            // {\"morning\":{\"from\":\"09:30:00\",\"to\":\"09:45:00\"},
            //   \"lunch\":{\"from\":\"13:00:00\",\"to\":\"13:30:00\"},
            //   \"evening\":{\"from\":\"17:15:00\",\"to\":\"17:30:00\"}}
            $table->json('breaks')->nullable();

            $table->boolean('recurring')->default(true);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->text('description')->nullable();

            $table->timestamps();

            // Uniqueness of shift names per tenant helps avoid duplicates
            $table->unique(['tenant_id', 'name']);
            $table->index('tenant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};

