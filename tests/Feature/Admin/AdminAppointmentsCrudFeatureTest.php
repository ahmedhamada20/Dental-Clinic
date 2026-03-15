<?php

use App\Models\Appointment\Appointment;
use Tests\Support\AdminFeatureTestHelpers;

uses(AdminFeatureTestHelpers::class);

beforeEach(function () {
    $this->seedAdminFeaturePermissions();
    $this->admin = $this->createAdminUser([
        'appointments.view',
        'appointments.create',
        'appointments.edit',
        'appointments.delete',
    ]);

    [$this->specialty, $this->category, $this->service] = $this->createSpecialtyCategoryService();
    $this->patient = $this->createPatient();
    $this->doctor = $this->createDentist($this->specialty);
});

it('covers appointments index create show and edit pages', function () {
    $appointment = Appointment::query()->create([
        'appointment_no' => 'APT-TEST100',
        'patient_id' => $this->patient->id,
        'specialty_id' => $this->specialty->id,
        'service_id' => $this->service->id,
        'assigned_doctor_id' => $this->doctor->id,
        'appointment_date' => now()->addDay()->toDateString(),
        'start_time' => '10:00',
        'end_time' => '10:30',
        'status' => 'pending',
        'booking_source' => 'web_app',
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.appointments.index'))
        ->assertOk();

    $this->actingAs($this->admin)
        ->get(route('admin.appointments.create'))
        ->assertOk()
        ->assertSee(route('admin.appointments.index'), false);

    $this->actingAs($this->admin)
        ->get(route('admin.appointments.show', $appointment))
        ->assertOk();

    $this->actingAs($this->admin)
        ->get(route('admin.appointments.edit', $appointment))
        ->assertOk()
        ->assertSee(route('admin.appointments.show', $appointment), false);
});

it('supports appointments filter and pagination actions', function () {
    for ($i = 1; $i <= 24; $i++) {
        Appointment::query()->create([
            'appointment_no' => 'APT-T' . str_pad((string) $i, 5, '0', STR_PAD_LEFT),
            'patient_id' => $this->patient->id,
            'specialty_id' => $this->specialty->id,
            'service_id' => $this->service->id,
            'assigned_doctor_id' => $this->doctor->id,
            'appointment_date' => now()->addDay()->toDateString(),
            'start_time' => '09:00',
            'end_time' => '09:30',
            'status' => 'pending',
            'booking_source' => 'web_app',
        ]);
    }

    $this->actingAs($this->admin)
        ->get(route('admin.appointments.index', [
            'status' => 'pending',
            'date' => now()->addDay()->toDateString(),
            'specialty_id' => $this->specialty->id,
            'page' => 1,
        ]))
        ->assertOk()
        ->assertSee('page=2', false);
});
