<?php

namespace Database\Seeders\Phase1;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * RoleAndPermissionSeeder
 *
 * Seeds roles and permissions for the dental clinic system.
 * Uses Spatie's laravel-permission package with idempotent logic.
 *
 * Roles:
 * - admin: Full system access
 * - doctor: Medical operations and patient treatment
 * - receptionist: Appointment and patient management
 * - accountant: Billing and financial operations
 */
class RoleAndPermissionSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()['cache']->forget('spatie.permission.cache');

        // Define all permissions grouped by feature
        $permissions = $this->getPermissions();

        // Create all permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                ['guard_name' => 'web']
            );
        }

        // Define roles and their permissions
        $roles = $this->getRolesWithPermissions();

        foreach ($roles as $roleData) {
            $role = Role::firstOrCreate(
                ['name' => $roleData['name']],
                ['guard_name' => 'web']
            );

            // Sync permissions for this role
            $permissionNames = array_column($roleData['permissions'], 'name');
            $permissionIds = Permission::whereIn('name', $permissionNames)->pluck('id');
            $role->permissions()->sync($permissionIds);
        }
    }

    /**
     * Get all permission definitions.
     *
     * @return array
     */
    private function getPermissions(): array
    {
        return [
            // User Management
            ['name' => 'users.view'],
            ['name' => 'users.create'],
            ['name' => 'users.edit'],
            ['name' => 'users.delete'],
            ['name' => 'users.manage-roles'],

            // Appointment Management
            ['name' => 'appointments.view'],
            ['name' => 'appointments.create'],
            ['name' => 'appointments.edit'],
            ['name' => 'appointments.delete'],
            ['name' => 'appointments.confirm'],
            ['name' => 'appointments.cancel'],

            // Visit Management
            ['name' => 'visits.view'],
            ['name' => 'visits.create'],
            ['name' => 'visits.edit'],
            ['name' => 'visits.notes'],
            ['name' => 'visits.check-in'],
            ['name' => 'visits.complete'],

            // Patient Management
            ['name' => 'patients.view'],
            ['name' => 'patients.create'],
            ['name' => 'patients.edit'],
            ['name' => 'patients.delete'],
            ['name' => 'patients.view-medical-history'],
            ['name' => 'patients.manage-medical-history'],

            // Medical Operations
            ['name' => 'odontogram.view'],
            ['name' => 'odontogram.edit'],
            ['name' => 'treatment-plans.view'],
            ['name' => 'treatment-plans.create'],
            ['name' => 'treatment-plans.edit'],
            ['name' => 'treatment-plans.approve'],
            ['name' => 'prescriptions.view'],
            ['name' => 'prescriptions.create'],
            ['name' => 'prescriptions.edit'],

            // Billing & Payments
            ['name' => 'invoices.view'],
            ['name' => 'invoices.create'],
            ['name' => 'invoices.edit'],
            ['name' => 'invoices.delete'],
            ['name' => 'invoices.send'],
            ['name' => 'payments.view'],
            ['name' => 'payments.create'],
            ['name' => 'payments.edit'],
            ['name' => 'payments.delete'],

            // Clinic Settings
            ['name' => 'clinic-settings.view'],
            ['name' => 'clinic-settings.edit'],
            ['name' => 'working-days.edit'],
            ['name' => 'services.view'],
            ['name' => 'services.manage'],

            // Reports
            ['name' => 'reports.view'],
            ['name' => 'reports.financial'],
            ['name' => 'reports.appointments'],
            ['name' => 'reports.patients'],

            // System
            ['name' => 'audit-logs.view'],
            ['name' => 'notifications.view'],
            ['name' => 'notifications.send'],

            // Dashboard & Top-Level Permissions
            ['name' => 'dashboard.view'],

            // Billing (top-level permission)
            ['name' => 'billing.view'],

            // Waiting List
            ['name' => 'waiting-list.view'],

            // Specialties
            ['name' => 'specialties.view'],
            ['name' => 'specialties.manage'],

            // Service Categories
            ['name' => 'service-categories.view'],
            ['name' => 'service-categories.manage'],

            // Services (top-level)
            ['name' => 'services.view'],

            // Treatment Plans (top-level)
            ['name' => 'treatment-plans.view'],

            // Prescriptions (top-level)
            ['name' => 'prescriptions.view'],

            // Promotions
            ['name' => 'promotions.view'],
            ['name' => 'promotions.manage'],

            // Roles
            ['name' => 'roles.view'],
            ['name' => 'roles.create'],
            ['name' => 'roles.edit'],
            ['name' => 'roles.delete'],

            // Settings
            ['name' => 'settings.view'],
            ['name' => 'settings.edit'],
        ];
    }

    /**
     * Get roles and their associated permissions.
     *
     * @return array
     */
    private function getRolesWithPermissions(): array
    {
        return [
            [
                'name' => 'admin',
                'permissions' => [
                    ['name' => 'dashboard.view'],
                    ['name' => 'users.view'],
                    ['name' => 'users.create'],
                    ['name' => 'users.edit'],
                    ['name' => 'users.delete'],
                    ['name' => 'users.manage-roles'],
                    ['name' => 'appointments.view'],
                    ['name' => 'appointments.create'],
                    ['name' => 'appointments.edit'],
                    ['name' => 'appointments.delete'],
                    ['name' => 'appointments.confirm'],
                    ['name' => 'appointments.cancel'],
                    ['name' => 'waiting-list.view'],
                    ['name' => 'visits.view'],
                    ['name' => 'visits.create'],
                    ['name' => 'visits.edit'],
                    ['name' => 'visits.notes'],
                    ['name' => 'visits.check-in'],
                    ['name' => 'visits.complete'],
                    ['name' => 'patients.view'],
                    ['name' => 'patients.create'],
                    ['name' => 'patients.edit'],
                    ['name' => 'patients.delete'],
                    ['name' => 'patients.view-medical-history'],
                    ['name' => 'patients.manage-medical-history'],
                    ['name' => 'specialties.view'],
                    ['name' => 'specialties.manage'],
                    ['name' => 'service-categories.view'],
                    ['name' => 'service-categories.manage'],
                    ['name' => 'odontogram.view'],
                    ['name' => 'odontogram.edit'],
                    ['name' => 'treatment-plans.view'],
                    ['name' => 'treatment-plans.create'],
                    ['name' => 'treatment-plans.edit'],
                    ['name' => 'treatment-plans.approve'],
                    ['name' => 'prescriptions.view'],
                    ['name' => 'prescriptions.create'],
                    ['name' => 'prescriptions.edit'],
                    ['name' => 'invoices.view'],
                    ['name' => 'invoices.create'],
                    ['name' => 'invoices.edit'],
                    ['name' => 'invoices.delete'],
                    ['name' => 'invoices.send'],
                    ['name' => 'payments.view'],
                    ['name' => 'payments.create'],
                    ['name' => 'payments.edit'],
                    ['name' => 'payments.delete'],
                    ['name' => 'billing.view'],
                    ['name' => 'promotions.view'],
                    ['name' => 'promotions.manage'],
                    ['name' => 'clinic-settings.view'],
                    ['name' => 'clinic-settings.edit'],
                    ['name' => 'working-days.edit'],
                    ['name' => 'services.view'],
                    ['name' => 'services.manage'],
                    ['name' => 'reports.view'],
                    ['name' => 'reports.financial'],
                    ['name' => 'reports.appointments'],
                    ['name' => 'reports.patients'],
                    ['name' => 'audit-logs.view'],
                    ['name' => 'notifications.view'],
                    ['name' => 'notifications.send'],
                    ['name' => 'roles.view'],
                    ['name' => 'roles.create'],
                    ['name' => 'roles.edit'],
                    ['name' => 'roles.delete'],
                    ['name' => 'settings.view'],
                    ['name' => 'settings.edit'],
                ],
            ],
            [
                'name' => 'doctor',
                'permissions' => [
                    ['name' => 'dashboard.view'],
                    ['name' => 'appointments.view'],
                    ['name' => 'appointments.edit'],
                    ['name' => 'appointments.confirm'],
                    ['name' => 'appointments.cancel'],
                    ['name' => 'waiting-list.view'],
                    ['name' => 'visits.view'],
                    ['name' => 'visits.create'],
                    ['name' => 'visits.edit'],
                    ['name' => 'visits.notes'],
                    ['name' => 'visits.check-in'],
                    ['name' => 'visits.complete'],
                    ['name' => 'patients.view'],
                    ['name' => 'patients.view-medical-history'],
                    ['name' => 'patients.manage-medical-history'],
                    ['name' => 'specialties.view'],
                    ['name' => 'service-categories.view'],
                    ['name' => 'odontogram.view'],
                    ['name' => 'odontogram.edit'],
                    ['name' => 'treatment-plans.view'],
                    ['name' => 'treatment-plans.create'],
                    ['name' => 'treatment-plans.edit'],
                    ['name' => 'treatment-plans.approve'],
                    ['name' => 'prescriptions.view'],
                    ['name' => 'prescriptions.create'],
                    ['name' => 'prescriptions.edit'],
                    ['name' => 'invoices.view'],
                    ['name' => 'reports.view'],
                    ['name' => 'reports.patients'],
                    ['name' => 'notifications.view'],
                ],
            ],
            [
                'name' => 'dentist',
                'permissions' => [
                    ['name' => 'dashboard.view'],
                    ['name' => 'appointments.view'],
                    ['name' => 'appointments.edit'],
                    ['name' => 'appointments.confirm'],
                    ['name' => 'appointments.cancel'],
                    ['name' => 'waiting-list.view'],
                    ['name' => 'visits.view'],
                    ['name' => 'visits.create'],
                    ['name' => 'visits.edit'],
                    ['name' => 'visits.notes'],
                    ['name' => 'visits.check-in'],
                    ['name' => 'visits.complete'],
                    ['name' => 'patients.view'],
                    ['name' => 'patients.view-medical-history'],
                    ['name' => 'patients.manage-medical-history'],
                    ['name' => 'specialties.view'],
                    ['name' => 'service-categories.view'],
                    ['name' => 'odontogram.view'],
                    ['name' => 'odontogram.edit'],
                    ['name' => 'treatment-plans.view'],
                    ['name' => 'treatment-plans.create'],
                    ['name' => 'treatment-plans.edit'],
                    ['name' => 'treatment-plans.approve'],
                    ['name' => 'prescriptions.view'],
                    ['name' => 'prescriptions.create'],
                    ['name' => 'prescriptions.edit'],
                    ['name' => 'invoices.view'],
                    ['name' => 'reports.view'],
                    ['name' => 'reports.patients'],
                    ['name' => 'notifications.view'],
                ],
            ],
            [
                'name' => 'receptionist',
                'permissions' => [
                    ['name' => 'dashboard.view'],
                    ['name' => 'appointments.view'],
                    ['name' => 'appointments.create'],
                    ['name' => 'appointments.edit'],
                    ['name' => 'appointments.confirm'],
                    ['name' => 'appointments.cancel'],
                    ['name' => 'waiting-list.view'],
                    ['name' => 'visits.view'],
                    ['name' => 'visits.check-in'],
                    ['name' => 'patients.view'],
                    ['name' => 'patients.create'],
                    ['name' => 'patients.edit'],
                    ['name' => 'patients.view-medical-history'],
                    ['name' => 'specialties.view'],
                    ['name' => 'service-categories.view'],
                    ['name' => 'services.view'],
                    ['name' => 'billing.view'],
                    ['name' => 'invoices.view'],
                    ['name' => 'invoices.create'],
                    ['name' => 'invoices.edit'],
                    ['name' => 'payments.view'],
                    ['name' => 'payments.create'],
                    ['name' => 'reports.view'],
                    ['name' => 'reports.appointments'],
                    ['name' => 'notifications.view'],
                ],
            ],
            [
                'name' => 'accountant',
                'permissions' => [
                    ['name' => 'dashboard.view'],
                    ['name' => 'billing.view'],
                    ['name' => 'invoices.view'],
                    ['name' => 'invoices.create'],
                    ['name' => 'invoices.edit'],
                    ['name' => 'invoices.send'],
                    ['name' => 'payments.view'],
                    ['name' => 'payments.create'],
                    ['name' => 'payments.edit'],
                    ['name' => 'patients.view'],
                    ['name' => 'appointments.view'],
                    ['name' => 'specialties.view'],
                    ['name' => 'service-categories.view'],
                    ['name' => 'services.view'],
                    ['name' => 'clinic-settings.view'],
                    ['name' => 'reports.view'],
                    ['name' => 'reports.financial'],
                    ['name' => 'notifications.view'],
                ],
            ],
            [
                'name' => 'assistant',
                'permissions' => [
                    ['name' => 'dashboard.view'],
                    ['name' => 'appointments.view'],
                    ['name' => 'visits.view'],
                    ['name' => 'visits.notes'],
                    ['name' => 'patients.view'],
                    ['name' => 'patients.view-medical-history'],
                    ['name' => 'patients.manage-medical-history'],
                    ['name' => 'notifications.view'],
                ],
            ],
        ];
    }
}

