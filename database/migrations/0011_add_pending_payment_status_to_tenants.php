<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("ALTER TABLE tenants MODIFY COLUMN status ENUM('pending_payment','provisioning','active','suspended','terminating','terminated') DEFAULT 'provisioning'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE tenants MODIFY COLUMN status ENUM('provisioning','active','suspended','terminating','terminated') DEFAULT 'provisioning'");
    }
};
