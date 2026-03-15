<?php

namespace Database\Seeders\Phase1;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Phase1DatabaseSeeder
 *
 * Orchestrates PHASE 1 seeding: Core Fixed Data initialization.
 *
 * Execution Order:
 * 1. RoleAndPermissionSeeder - Spatie roles and permissions (required for admin assignment)
 * 2. AdminUserSeeder - Default admin user (may use roles from #1)
 * 3. ClinicSettingSeeder - Key-value clinic configuration
 * 4. WorkingDayAndHourSeeder - Default working days and hours
 * 5. SystemStatusSeeder - Validates system status enums
 * 6. NotificationTypeSeeder - Validates notification types
 *
 * All seeders are idempotent and can be safely re-run without causing duplicates.
 */
class Phase1DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Core permissions and roles first
        $this->call(RoleAndPermissionSeeder::class);

        // Admin user with role assignment
        $this->call(AdminUserSeeder::class);

        // Clinic configuration
        $this->call(ClinicSettingSeeder::class);

        // Working schedule
        $this->call(WorkingDayAndHourSeeder::class);

        // System status validation
        $this->call(SystemStatusSeeder::class);

        // Notification type validation
        $this->call(NotificationTypeSeeder::class);

        $this->command->info('✓ Phase 1 seeding complete: Core fixed data initialized');
    }
}

