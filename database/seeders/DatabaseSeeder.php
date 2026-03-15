<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\Phase1\AdminUserSeeder;
use Database\Seeders\Phase1\ClinicSettingSeeder;
use Database\Seeders\Phase1\MedicalSpecialtySeeder;
use Database\Seeders\Phase1\NotificationTypeSeeder;
use Database\Seeders\Phase1\RoleAndPermissionSeeder;
use Database\Seeders\Phase1\ServiceCatalogSeeder;
use Database\Seeders\Phase1\SystemStatusSeeder;
use Database\Seeders\Phase1\WorkingDayAndHourSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $this->call([
            RoleAndPermissionSeeder::class,
            AdminUserSeeder::class,
            ClinicSettingSeeder::class,
            WorkingDayAndHourSeeder::class,
            SystemStatusSeeder::class,
            NotificationTypeSeeder::class,
            MedicalSpecialtySeeder::class,
            ServiceCatalogSeeder::class,
            Phase3PatientsSeeder::class,
            ProjectDemoSeeder::class,
        ]);
    }
}
