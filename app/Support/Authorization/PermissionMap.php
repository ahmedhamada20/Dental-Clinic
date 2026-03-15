<?php

namespace App\Support\Authorization;

class PermissionMap
{
    public static function aliases(): array
    {
        return [
            'manage_patients' => ['patients.view', 'patients.create', 'patients.edit', 'patients.delete', 'patients.manage-medical-history'],
            'manage_users' => ['users.view', 'users.create', 'users.edit', 'users.delete', 'users.manage-roles'],
            'manage_roles' => ['roles.view', 'roles.create', 'roles.edit', 'roles.delete'],
            'view-patients' => ['patients.view'],
            'view-appointments' => ['appointments.view'],
            'view-waiting-list' => ['waiting-list.view'],
            'view-visits' => ['visits.view'],
            'view-services' => ['services.view', 'service-categories.view'],
            'view-treatment-plans' => ['treatment-plans.view'],
            'view-prescriptions' => ['prescriptions.view'],
            'view-billing' => ['billing.view', 'invoices.view', 'payments.view'],
            'view-promotions' => ['promotions.view'],
            'view-notifications' => ['notifications.view'],
            'view-reports' => ['reports.view'],
            'view-settings' => ['settings.view', 'clinic-settings.view'],
            'view-users' => ['users.view'],
            'view-roles' => ['roles.view'],
            'view-audit-logs' => ['audit-logs.view'],
        ];
    }

    public static function resolve(string $ability): array
    {
        $resolved = [$ability];

        // Only expand explicit aliases if they are actually defined
        if (isset(self::aliases()[$ability])) {
            foreach (self::aliases()[$ability] as $alias) {
                $resolved[] = $alias;
            }
        }

        // Note: Format conversion (dot <-> dash) is removed to prevent unintended expansion
        // Only explicitly defined aliases are expanded. This prevents users with only
        // "patients.view" from accidentally gaining "patients-view" or vice versa.

        return array_values(array_unique(array_filter($resolved)));
    }
}

