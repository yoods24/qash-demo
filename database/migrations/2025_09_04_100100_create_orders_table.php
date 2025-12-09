<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreignId('customer_detail_id')->constrained('customer_details');
            $table->decimal('total', 10, 2);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('total_tax', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2)->default(0);
            $table->enum('status', ['waiting_for_payment', 'confirmed', 'preparing', 'ready', 'served', 'cancelled'])->default('waiting_for_payment');
            // Order origin (POS cashier vs table QR)
            $table->enum('source', ['pos', 'qr'])->default('pos');
            $table->enum('order_type', ['dine-in','takeaway'])->default('dine-in');

            $table->enum('payment_status', ['pending','paid', 'failed', 'cancelled'])->default('pending');
            $table->string('payment_channel', 120)->nullable();

            $table->string('xendit_invoice_id')->nullable();
            $table->string('xendit_invoice_url')->nullable();
            // Order type (service intention)
            $table->string('reference_no')->nullable();
            $table->index(['tenant_id', 'reference_no']);
            $table->index(['tenant_id', 'source']);
            $table->index(['tenant_id', 'order_type']);
            // Timing + performance fields
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('preparing_at')->nullable();
            $table->timestamp('ready_at')->nullable();
            // Expected duration for full order (sum of item estimates)
            $table->integer('expected_seconds_total')->default(0);
            // Actual durations
            $table->integer('queue_seconds')->default(0);   // confirmed -> preparing
            $table->integer('prep_seconds')->default(0);    // preparing -> ready
            $table->integer('total_seconds')->default(0);   // full lifecycle
            // Responsibility fields removed — kitchen works as a single entity
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
