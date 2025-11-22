<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_invoice_settings', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->string('invoice_logo')->nullable();
            $table->string('invoice_prefix', 50)->nullable();
            $table->unsignedTinyInteger('invoice_due_days')->default(0);
            $table->boolean('invoice_round_off')->default(false);
            $table->enum('invoice_round_direction', ['up', 'down'])->default('up');
            $table->boolean('show_company_details')->default(true);
            $table->text('invoice_header_terms')->nullable();
            $table->text('invoice_footer_terms')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->unique('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_invoice_settings');
    }
};
