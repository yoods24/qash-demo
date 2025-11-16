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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants');
            $table->foreignId('floor_id')->nullable()->constrained('floors')->nullOnDelete();
            $table->string('title');
            $table->text('description');
            $table->enum('event_type', [
                'entertainment',
                'announcement',
                'promotions',
                'special_event',
                'workshop',
                'community',
                'operational',
            ]);
            $table->date('date');
            $table->time('time');
            $table->string('location')->nullable();
            $table->longText('about')->nullable();
            $table->longText('event_highlights')->nullable();
            $table->longText('what_to_expect')->nullable();
            $table->integer('capacity')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
            $table->index(['tenant_id', 'date']);
            $table->index('event_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
