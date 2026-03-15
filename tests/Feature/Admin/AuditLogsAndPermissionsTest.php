<?php

use App\Models\Appointment\Appointment;
use App\Models\Patient\Patient;
use App\Models\System\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    app()['cache']->forget('spatie.permission.cache');

    $this->admin = User::query()->create([
        'first_name' => 'Audit',
        'last_name' => 'Admin',
        'full_name' => 'Audit Admin',
        'email' => 'audit-admin@example.com',
        'phone' => '5557001',
        'password' => Hash::make('password'),
        'user_type' => 'admin',
        'status' => 'active',
    ]);

    Permission::findOrCreate('audit-logs.view', 'web');
    Permission::findOrCreate('appointments.view', 'web');
    Permission::findOrCreate('patients.view', 'web');
    Permission::findOrCreate('patients.create', 'web');
    Permission::findOrCreate('patients.edit', 'web');
    Permission::findOrCreate('patients.delete', 'web');
    Permission::findOrCreate('patients.manage-medical-history', 'web');
});

it('maps legacy and ui permission aliases through the spatie permission layer', function () {
    $this->admin->givePermissionTo(['patients.view', 'patients.create']);

    expect($this->admin->can('view-patients'))->toBeTrue()
        ->and($this->admin->can('manage_patients'))->toBeTrue()
        ->and($this->admin->can('audit-logs.view'))->toBeFalse();
});

it('writes audit logs for appointment create actions and shows them in the admin review page', function () {
    $this->admin->givePermissionTo(['appointments.view', 'audit-logs.view']);

    $patient = Patient::query()->create([
        'first_name' => 'Lara',
        'last_name' => 'Stone',
        'full_name' => 'Lara Stone',
        'phone' => '5557100',
        'email' => 'lara@example.com',
        'gender' => 'female',
        'date_of_birth' => '1994-04-10',
        'age' => 31,
        'status' => 'active',
        'registered_from' => 'dashboard',
    ]);

    $response = $this->actingAs($this->admin)->post(route('admin.appointments.store'), [
        'patient_id' => $patient->id,
        'assigned_doctor_id' => null,
        'service_id' => null,
        'appointment_date' => now()->addDay()->toDateString(),
        'appointment_time' => '10:00',
        'status' => 'pending',
        'notes' => 'Audit trail test',
    ]);

    $response->assertRedirect(route('admin.appointments.index'));

    $log = AuditLog::query()->where('module', 'appointments')->where('action', 'create')->latest('id')->first();

    expect($log)->not->toBeNull()
        ->and($log->actor_id)->toBe($this->admin->id)
        ->and($log->new_values['patient_id'] ?? null)->toBe($patient->id);

    $page = $this->actingAs($this->admin)->get(route('admin.audit-logs.index'));

    $page->assertOk()
        ->assertSee('Audit Logs')
        ->assertSee('appointments')
        ->assertSee('create');
});

it('shows an audit log detail page to authorized admins', function () {
    $this->admin->givePermissionTo(['audit-logs.view']);

    $appointment = Appointment::query()->create([
        'patient_id' => null,
        'service_id' => null,
        'assigned_doctor_id' => null,
        'appointment_date' => now()->addDays(2)->toDateString(),
        'start_time' => '09:00:00',
        'end_time' => '09:30:00',
        'status' => 'pending',
        'notes' => 'Manual audit seed',
    ]);

    $log = AuditLog::query()->create([
        'actor_type' => 'User',
        'actor_id' => $this->admin->id,
        'action' => 'create',
        'module' => 'appointments',
        'entity_type' => Appointment::class,
        'entity_id' => $appointment->id,
        'old_values' => null,
        'new_values' => ['notes' => 'Manual audit seed'],
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Pest',
    ]);

    $response = $this->actingAs($this->admin)->get(route('admin.audit-logs.show', $log->id));

    $response->assertOk()
        ->assertSee('Audit Log #'.$log->id)
        ->assertSee('Manual audit seed');
});

