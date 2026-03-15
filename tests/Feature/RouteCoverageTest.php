<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

describe('Route Coverage - Guest Access', function () {
    it('allows guest to access welcome page', function () {
        $response = $this->get('/');
        $response->assertStatus(200);
    });

    it('redirects guest to login for dashboard', function () {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    });

    it('redirects guest to login for patients', function () {
        $response = $this->get('/patients');
        $response->assertRedirect('/login');
    });

    it('redirects guest to login for appointments', function () {
        $response = $this->get('/admin/appointments');
        $response->assertRedirect('/login');
    });

    it('redirects guest to login for waiting list', function () {
        $response = $this->get('/waiting-list');
        $response->assertRedirect('/login');
    });

    it('redirects guest to login for visits', function () {
        $response = $this->get('/visits');
        $response->assertRedirect('/login');
    });

    it('redirects guest to login for billing', function () {
        $response = $this->get('/billing');
        $response->assertRedirect('/login');
    });

    it('redirects guest to login for reports', function () {
        $response = $this->get('/admin/reports');
        $response->assertRedirect('/login');
    });

    it('redirects guest to login for settings', function () {
        $response = $this->get('/settings');
        $response->assertRedirect('/login');
    });

    it('redirects guest to login for users', function () {
        $response = $this->get('/users');
        $response->assertRedirect('/login');
    });

    it('redirects guest to login for roles', function () {
        $response = $this->get('/roles');
        $response->assertRedirect('/login');
    });

    it('redirects guest to login for notifications', function () {
        $response = $this->get('/admin/notifications');
        $response->assertRedirect('/login');
    });
});

describe('Route Coverage - Authenticated Admin', function () {
    beforeEach(function () {
        // Create a role with all permissions
        $role = Role::create(['name' => 'admin', 'guard_name' => 'web']);

        // Create all permissions needed for the routes
        $permissions = [
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
            'visits.create',
            'visits.edit',
            'visits.check-in',
            'visits.complete',
            'specialties.view',
            'specialties.manage',
            'service-categories.view',
            'service-categories.manage',
            'services.view',
            'services.manage',
            'treatment-plans.view',
            'prescriptions.view',
            'billing.view',
            'invoices.view',
            'invoices.create',
            'invoices.edit',
            'invoices.delete',
            'payments.view',
            'payments.create',
            'payments.delete',
            'promotions.view',
            'promotions.manage',
            'notifications.view',
            'notifications.send',
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
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
            $role->givePermissionTo($permission);
        }

        // Create authenticated user with admin role
        $this->user = User::factory()->create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@test.com',
        ]);
        $this->user->assignRole($role);
    });

    // Dashboard
    it('admin can access dashboard', function () {
        $response = $this->actingAs($this->user)->get('/dashboard');
        $response->assertStatus(200);
    });

    // Patients Routes
    it('admin can access patients list', function () {
        $response = $this->actingAs($this->user)->get('/patients');
        $response->assertStatus(200);
    });

    it('admin can access patients create form', function () {
        $response = $this->actingAs($this->user)->get('/patients/create');
        $response->assertStatus(200);
    });

    // Appointments Routes
    it('admin can access appointments list', function () {
        $response = $this->actingAs($this->user)->get('/admin/appointments');
        $response->assertStatus(200);
    });

    it('admin can access appointments create form', function () {
        $response = $this->actingAs($this->user)->get('/admin/appointments/create');
        $response->assertStatus(200);
    });

    // Waiting List Routes
    it('admin can access waiting list', function () {
        $response = $this->actingAs($this->user)->get('/waiting-list');
        $response->assertStatus(200);
    });

    it('admin can access waiting list create form', function () {
        $response = $this->actingAs($this->user)->get('/waiting-list/create');
        $response->assertStatus(200);
    });

    // Visits Routes
    it('admin can access visits list', function () {
        $response = $this->actingAs($this->user)->get('/visits');
        $response->assertStatus(200);
    });

    it('admin can access visits create form', function () {
        $response = $this->actingAs($this->user)->get('/visits/create');
        $response->assertStatus(200);
    });

    it('admin can access visits queue', function () {
        $response = $this->actingAs($this->user)->get('/visits/queue/today');
        $response->assertStatus(200);
    });

    // Medical Specialties Routes
    it('admin can access medical specialties list', function () {
        $response = $this->actingAs($this->user)->get('/admin/specialties');
        $response->assertStatus(200);
    });

    it('admin can access specialties create form', function () {
        $response = $this->actingAs($this->user)->get('/admin/specialties/create');
        $response->assertStatus(200);
    });

    // Service Categories Routes
    it('admin can access service categories list', function () {
        $response = $this->actingAs($this->user)->get('/admin/service-categories');
        $response->assertStatus(200);
    });

    it('admin can access service categories create form', function () {
        $response = $this->actingAs($this->user)->get('/admin/service-categories/create');
        $response->assertStatus(200);
    });

    // Services Routes
    it('admin can access services list', function () {
        $response = $this->actingAs($this->user)->get('/admin/services');
        $response->assertStatus(200);
    });

    it('admin can access services create form', function () {
        $response = $this->actingAs($this->user)->get('/admin/services/create');
        $response->assertStatus(200);
    });

    // Treatment Plans Routes
    it('admin can access treatment plans list', function () {
        $response = $this->actingAs($this->user)->get('/admin/treatment-plans');
        $response->assertStatus(200);
    });

    // Prescriptions Routes
    it('admin can access prescriptions list', function () {
        $response = $this->actingAs($this->user)->get('/admin/prescriptions');
        $response->assertStatus(200);
    });

    // Billing Routes
    it('admin can access billing dashboard', function () {
        $response = $this->actingAs($this->user)->get('/billing');
        $response->assertStatus(200);
    });

    it('admin can access invoices list', function () {
        $response = $this->actingAs($this->user)->get('/billing/invoices');
        $response->assertStatus(200);
    });

    it('admin can access invoices create form', function () {
        $response = $this->actingAs($this->user)->get('/billing/invoices/create');
        $response->assertStatus(200);
    });

    it('admin can access payments list', function () {
        $response = $this->actingAs($this->user)->get('/billing/payments');
        $response->assertStatus(200);
    });

    // Promotions Routes
    it('admin can access promotions list', function () {
        $response = $this->actingAs($this->user)->get('/admin/promotions');
        $response->assertStatus(200);
    });

    it('admin can access promotions create form', function () {
        $response = $this->actingAs($this->user)->get('/admin/promotions/create');
        $response->assertStatus(200);
    });

    // Notifications Routes
    it('admin can access notifications list', function () {
        $response = $this->actingAs($this->user)->get('/admin/notifications');
        $response->assertStatus(200);
    });

    it('admin can access notifications create form', function () {
        $response = $this->actingAs($this->user)->get('/admin/notifications/create');
        $response->assertStatus(200);
    });

    // Reports Routes
    it('admin can access reports', function () {
        $response = $this->actingAs($this->user)->get('/admin/reports');
        $response->assertStatus(200);
    });

    // Settings Routes
    it('admin can access settings', function () {
        $response = $this->actingAs($this->user)->get('/settings');
        $response->assertStatus(200);
    });

    // Users Routes
    it('admin can access users list', function () {
        $response = $this->actingAs($this->user)->get('/users');
        $response->assertStatus(200);
    });

    it('admin can access users create form', function () {
        $response = $this->actingAs($this->user)->get('/users/create');
        $response->assertStatus(200);
    });

    // Roles Routes
    it('admin can access roles list', function () {
        $response = $this->actingAs($this->user)->get('/roles');
        $response->assertStatus(200);
    });

    // Audit Logs Routes
    it('admin can access audit logs list', function () {
        $response = $this->actingAs($this->user)->get('/admin/audit-logs');
        $response->assertStatus(200);
    });

    // Profile Routes
    it('authenticated user can edit profile', function () {
        $response = $this->actingAs($this->user)->get('/profile');
        $response->assertStatus(200);
    });
});

describe('Route Coverage - Authorization Failures', function () {
    beforeEach(function () {
        // Create a user without permissions
        $this->user = User::factory()->create([
            'first_name' => 'Limited',
            'last_name' => 'User',
            'email' => 'limited@test.com',
        ]);
    });

    it('user without permissions gets 403 for dashboard', function () {
        $response = $this->actingAs($this->user)->get('/dashboard');
        expect($response->status())->toBeIn([403, 401]);
    });

    it('user without permissions gets 403 for patients', function () {
        $response = $this->actingAs($this->user)->get('/patients');
        expect($response->status())->toBeIn([403, 401]);
    });

    it('user without permissions gets 403 for appointments', function () {
        $response = $this->actingAs($this->user)->get('/admin/appointments');
        expect($response->status())->toBeIn([403, 401]);
    });

    it('user without permissions gets 403 for billing', function () {
        $response = $this->actingAs($this->user)->get('/billing');
        expect($response->status())->toBeIn([403, 401]);
    });

    it('user without permissions gets 403 for reports', function () {
        $response = $this->actingAs($this->user)->get('/admin/reports');
        expect($response->status())->toBeIn([403, 401]);
    });

    it('user without permissions gets 403 for settings', function () {
        $response = $this->actingAs($this->user)->get('/settings');
        expect($response->status())->toBeIn([403, 401]);
    });

    it('user without permissions gets 403 for users', function () {
        $response = $this->actingAs($this->user)->get('/users');
        expect($response->status())->toBeIn([403, 401]);
    });

    it('user without permissions gets 403 for roles', function () {
        $response = $this->actingAs($this->user)->get('/roles');
        expect($response->status())->toBeIn([403, 401]);
    });
});

describe('No 500 Errors', function () {
    beforeEach(function () {
        // Setup admin user
        $role = Role::create(['name' => 'admin', 'guard_name' => 'web']);

        $permissions = [
            'dashboard.view', 'patients.view', 'patients.create', 'patients.edit', 'patients.delete',
            'patients.manage-medical-history', 'appointments.view', 'appointments.create', 'appointments.edit',
            'appointments.delete', 'waiting-list.view', 'visits.view', 'visits.create', 'visits.edit',
            'visits.check-in', 'visits.complete', 'specialties.view', 'specialties.manage',
            'service-categories.view', 'service-categories.manage', 'services.view', 'services.manage',
            'treatment-plans.view', 'prescriptions.view', 'billing.view', 'invoices.view', 'invoices.create',
            'invoices.edit', 'invoices.delete', 'payments.view', 'payments.create', 'payments.delete',
            'promotions.view', 'promotions.manage', 'notifications.view', 'notifications.send',
            'reports.view', 'settings.view', 'settings.edit', 'users.view', 'users.create',
            'users.edit', 'users.delete', 'roles.view', 'roles.create', 'roles.edit', 'roles.delete',
            'audit-logs.view',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
            $role->givePermissionTo($permission);
        }

        $this->adminUser = User::factory()->create([
            'first_name' => 'Admin',
            'last_name' => 'Test',
            'email' => 'admin@test.com',
        ]);
        $this->adminUser->assignRole($role);
    });

    it('dashboard does not return 500', function () {
        $response = $this->actingAs($this->adminUser)->get('/dashboard');
        expect($response->status())->not()->toBe(500);
    });

    it('patients index does not return 500', function () {
        $response = $this->actingAs($this->adminUser)->get('/patients');
        expect($response->status())->not()->toBe(500);
    });

    it('appointments index does not return 500', function () {
        $response = $this->actingAs($this->adminUser)->get('/admin/appointments');
        expect($response->status())->not()->toBe(500);
    });

    it('waiting list does not return 500', function () {
        $response = $this->actingAs($this->adminUser)->get('/waiting-list');
        expect($response->status())->not()->toBe(500);
    });

    it('visits index does not return 500', function () {
        $response = $this->actingAs($this->adminUser)->get('/visits');
        expect($response->status())->not()->toBe(500);
    });

    it('billing does not return 500', function () {
        $response = $this->actingAs($this->adminUser)->get('/billing');
        expect($response->status())->not()->toBe(500);
    });

    it('reports does not return 500', function () {
        $response = $this->actingAs($this->adminUser)->get('/admin/reports');
        expect($response->status())->not()->toBe(500);
    });

    it('notifications does not return 500', function () {
        $response = $this->actingAs($this->adminUser)->get('/admin/notifications');
        expect($response->status())->not()->toBe(500);
    });

    it('settings does not return 500', function () {
        $response = $this->actingAs($this->adminUser)->get('/settings');
        expect($response->status())->not()->toBe(500);
    });

    it('users does not return 500', function () {
        $response = $this->actingAs($this->adminUser)->get('/users');
        expect($response->status())->not()->toBe(500);
    });

    it('roles does not return 500', function () {
        $response = $this->actingAs($this->adminUser)->get('/roles');
        expect($response->status())->not()->toBe(500);
    });
});

