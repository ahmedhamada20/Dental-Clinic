<?php

namespace Database\Seeders\Test;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class AdminFeaturePermissionSeeder extends Seeder
{
    /**
     * Permission set used by admin feature tests.
     */
    public static function permissions(): array
    {
        return [
            'dashboard.view',
            'patients.view',
            'patients.create',
            'patients.edit',
            'patients.delete',
            'patients.manage-medical-history',
            'appointments.view',
            'appointments.create',
            'appointments.edit',
            'appointments.delete',
            'waiting-list.view',
            'visits.view',
            'visits.notes',
            'specialties.view',
            'specialties.manage',
            'service-categories.view',
            'service-categories.manage',
            'services.view',
            'services.manage',
            'billing.view',
            'reports.view',
            'settings.view',
            'settings.edit',
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'roles.view',
            'roles.create',
            'roles.edit',
            'roles.delete',
            'audit-logs.view',
            'notifications.view',
            'notifications.send',
            'treatment-plans.view',
            'prescriptions.view',
            'promotions.view',
            'promotions.manage',
        ];
    }

    public function run(): void
    {
        foreach (self::permissions() as $permissionName) {
            Permission::findOrCreate($permissionName, 'web');
        }
    }
}

