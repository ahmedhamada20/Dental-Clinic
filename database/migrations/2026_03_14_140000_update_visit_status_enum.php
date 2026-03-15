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

        DB::statement("ALTER TABLE visits MODIFY COLUMN status ENUM('checked_in','with_doctor','scheduled','in_progress','completed','cancelled','no_show') NOT NULL DEFAULT 'scheduled'");
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE visits MODIFY COLUMN status ENUM('checked_in','with_doctor','completed','cancelled','no_show') NOT NULL DEFAULT 'checked_in'");
    }
};

