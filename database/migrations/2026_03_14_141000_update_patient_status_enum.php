<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE patients MODIFY COLUMN status ENUM('active','inactive','blocked','suspended','archived') NOT NULL DEFAULT 'active'");
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        // Revert unknown statuses to inactive before narrowing the enum
        DB::statement("UPDATE patients SET status = 'inactive' WHERE status IN ('suspended','archived')");
        DB::statement("ALTER TABLE patients MODIFY COLUMN status ENUM('active','inactive','blocked') NOT NULL DEFAULT 'active'");
    }
};

