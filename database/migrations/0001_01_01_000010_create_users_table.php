<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Stancl\Tenancy\Database\Models\Tenant;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            // Tenants use a string primary key; keep tenant_id as string FK
            $table->string('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreignId('shift_id')->nullable()->constrained('shifts')->nullOnDelete();
            // General Information
            $table->string('profile-image')->nullable();
            $table->string('firstName');
            $table->string('lastName');
            $table->string('email')->unique();
            $table->string('emp_code')->nullable()->unique();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['Male', 'Female']);
            $table->string('nationality')->nullable();
            $table->date('joining_date')->nullable();
            $table->enum('blood_group', ['O', 'A', 'B', 'AB']);
            $table->text('about')->nullable();
            $table->string('phone')->nullable();
            $table->string('password');

            // Address Information
            $table->string('address')->nullable();
            $table->string('country')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('zipcode')->nullable();


            // Emergency Information
            $table->string('emergency_contact_number_1')->nullable();
            $table->string('emergency_contact_relation_1')->nullable();
            $table->string('emergency_contact_name_1')->nullable();
            $table->string('emergency_contact_number_2')->nullable();
            $table->string('emergency_contact_relation_2')->nullable();
            $table->string('emergency_contact_name_2')->nullable();


            // Bank Information
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();


            $table->boolean('is_admin')->default(0);
            $table->boolean('status')->default(0);
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
