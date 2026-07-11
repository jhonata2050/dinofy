<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained();
            $table->string('subdomain')->unique();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('document')->nullable();
            $table->enum('status', ['provisioning', 'active', 'suspended', 'terminating', 'terminated'])->default('provisioning');
            $table->string('compose_project')->unique();
            $table->string('data_path');
            $table->text('db_password');
            $table->text('app_key');
            $table->string('custom_domain')->nullable()->unique();
            $table->date('next_billing_date')->nullable();
            $table->date('trial_ends_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
