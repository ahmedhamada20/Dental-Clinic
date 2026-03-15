<?php

use App\Models\User;
use App\Models\Appointment\Appointment;
use App\Models\Patient\Patient;
use Database\Factories\AppointmentFactory;
use Database\Factories\Patient\PatientFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

describe('Patient Routes with Data', function () {
    beforeEach(function () {
        // Create admin user with permissions
        $role = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $permissions = [
            'patients.view', 'patients.create', 'patients.edit', 'patients.delete',
            'patients.manage-medical-history'
        ];
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
            $role->givePermissionTo($permission);
        }

        $this->user = User::factory()->create();
        $this->user->assignRole($role);

        // Create test patient
        $this->patient = Patient::factory()->create();
    });

    it('can view patient list', function () {
        $response = $this->actingAs($this->user)->get('/patients');
        $response->assertStatus(200);
        $response->assertViewIs('admin.patients.index');
    });

    it('can view patient create form', function () {
        $response = $this->actingAs($this->user)->get('/patients/create');
        $response->assertStatus(200);
        $response->assertViewIs('admin.patients.create');
    });

    it('can store new patient', function () {
        $patientData = Patient::factory()->make()->toArray();

        $response = $this->actingAs($this->user)->post('/patients', $patientData);

        $response->assertRedirect();
        $this->assertDatabaseHas('patients', ['email' => $patientData['email']]);
    });

    it('can view individual patient', function () {
        $response = $this->actingAs($this->user)->get("/patients/{$this->patient->id}");
        $response->assertStatus(200);
        $response->assertViewIs('admin.patients.show');
    });

    it('can view patient edit form', function () {
        $response = $this->actingAs($this->user)->get("/patients/{$this->patient->id}/edit");
        $response->assertStatus(200);
        $response->assertViewIs('admin.patients.edit');
    });

    it('can update patient', function () {
        $newName = 'Updated Name';
        $response = $this->actingAs($this->user)->put("/patients/{$this->patient->id}", [
            ...$this->patient->toArray(),
            'first_name' => $newName,
        ]);

        $response->assertRedirect();
        $this->patient->refresh();
        expect($this->patient->first_name)->toBe($newName);
    });

    it('can delete patient', function () {
        $patientId = $this->patient->id;
        $response = $this->actingAs($this->user)->delete("/patients/{$patientId}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('patients', ['id' => $patientId]);
    });
});

describe('Appointment Routes with Data', function () {
    beforeEach(function () {
        // Create admin user with permissions
        $role = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $permissions = [
            'appointments.view', 'appointments.create', 'appointments.edit', 'appointments.delete'
        ];
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
            $role->givePermissionTo($permission);
        }

        $this->user = User::factory()->create();
        $this->user->assignRole($role);

        // Create test appointment
        $this->appointment = Appointment::factory()->create();
    });

    it('can view appointments list', function () {
        $response = $this->actingAs($this->user)->get('/admin/appointments');
        $response->assertStatus(200);
        $response->assertViewIs('admin.appointments.index');
    });

    it('can view appointment create form', function () {
        $response = $this->actingAs($this->user)->get('/admin/appointments/create');
        $response->assertStatus(200);
        $response->assertViewIs('admin.appointments.create');
    });

    it('can view individual appointment', function () {
        $response = $this->actingAs($this->user)->get("/admin/appointments/{$this->appointment->id}");
        $response->assertStatus(200);
        $response->assertViewIs('admin.appointments.show');
    });

    it('can view appointment edit form', function () {
        $response = $this->actingAs($this->user)->get("/admin/appointments/{$this->appointment->id}/edit");
        $response->assertStatus(200);
        $response->assertViewIs('admin.appointments.edit');
    });

    it('can delete appointment', function () {
        $appointmentId = $this->appointment->id;
        $response = $this->actingAs($this->user)->delete("/admin/appointments/{$appointmentId}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('appointments', ['id' => $appointmentId]);
    });
});

describe('Visits Routes with Data', function () {
    beforeEach(function () {
        // Create admin user with permissions
        $role = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $permissions = [
            'visits.view', 'visits.create', 'visits.edit', 'visits.check-in', 'visits.complete'
        ];
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
            $role->givePermissionTo($permission);
        }

        $this->user = User::factory()->create();
        $this->user->assignRole($role);
    });

    it('can view visits list', function () {
        $response = $this->actingAs($this->user)->get('/visits');
        $response->assertStatus(200);
        $response->assertViewIs('admin.visits.index');
    });

    it('can view visits create form', function () {
        $response = $this->actingAs($this->user)->get('/visits/create');
        $response->assertStatus(200);
        $response->assertViewIs('admin.visits.create');
    });

    it('can view visits queue for today', function () {
        $response = $this->actingAs($this->user)->get('/visits/queue/today');
        $response->assertStatus(200);
        $response->assertViewIs('admin.visits.queue');
    });
});

describe('Billing Routes', function () {
    beforeEach(function () {
        // Create admin user with permissions
        $role = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $permissions = [
            'billing.view', 'invoices.view', 'invoices.create', 'invoices.edit', 'invoices.delete',
            'payments.view', 'payments.create', 'payments.delete'
        ];
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
            $role->givePermissionTo($permission);
        }

        $this->user = User::factory()->create();
        $this->user->assignRole($role);
    });

    it('can view billing dashboard', function () {
        $response = $this->actingAs($this->user)->get('/billing');
        $response->assertStatus(200);
    });

    it('can view invoices list', function () {
        $response = $this->actingAs($this->user)->get('/billing/invoices');
        $response->assertStatus(200);
    });

    it('can view invoices create form', function () {
        $response = $this->actingAs($this->user)->get('/billing/invoices/create');
        $response->assertStatus(200);
    });

    it('can view payments list', function () {
        $response = $this->actingAs($this->user)->get('/billing/payments');
        $response->assertStatus(200);
    });
});

describe('Reports Routes', function () {
    beforeEach(function () {
        // Create admin user with permissions
        $role = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $permissions = ['reports.view'];
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
            $role->givePermissionTo($permission);
        }

        $this->user = User::factory()->create();
        $this->user->assignRole($role);
    });

    it('can view reports page', function () {
        $response = $this->actingAs($this->user)->get('/admin/reports');
        $response->assertStatus(200);
    });
});

describe('Settings Routes', function () {
    beforeEach(function () {
        // Create admin user with permissions
        $role = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $permissions = ['settings.view', 'settings.edit'];
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
            $role->givePermissionTo($permission);
        }

        $this->user = User::factory()->create();
        $this->user->assignRole($role);
    });

    it('can view settings', function () {
        $response = $this->actingAs($this->user)->get('/settings');
        $response->assertStatus(200);
    });
});

describe('Notifications Routes', function () {
    beforeEach(function () {
        // Create admin user with permissions
        $role = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $permissions = ['notifications.view', 'notifications.send'];
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
            $role->givePermissionTo($permission);
        }

        $this->user = User::factory()->create();
        $this->user->assignRole($role);
    });

    it('can view notifications list', function () {
        $response = $this->actingAs($this->user)->get('/admin/notifications');
        $response->assertStatus(200);
    });

    it('can view notification create form', function () {
        $response = $this->actingAs($this->user)->get('/admin/notifications/create');
        $response->assertStatus(200);
    });
});

describe('Users and Roles Routes', function () {
    beforeEach(function () {
        // Create admin user with permissions
        $role = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $permissions = [
            'users.view', 'users.create', 'users.edit', 'users.delete',
            'roles.view', 'roles.create', 'roles.edit', 'roles.delete'
        ];
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
            $role->givePermissionTo($permission);
        }

        $this->user = User::factory()->create();
        $this->user->assignRole($role);
    });

    it('can view users list', function () {
        $response = $this->actingAs($this->user)->get('/users');
        $response->assertStatus(200);
    });

    it('can view users create form', function () {
        $response = $this->actingAs($this->user)->get('/users/create');
        $response->assertStatus(200);
    });

    it('can view roles list', function () {
        $response = $this->actingAs($this->user)->get('/roles');
        $response->assertStatus(200);
    });
});

