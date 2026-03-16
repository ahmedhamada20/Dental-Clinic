<?php

use App\Enums\BookingSource;
use App\Enums\AppointmentStatus;
use App\Models\Appointment\Appointment;
use App\Models\Visit\Visit;
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
        'booking_source' => BookingSource::WEB_APP->value,
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

it('supports appointments calendar filters with patient direct link', function () {
    $targetPatient = $this->createPatient([
        'first_name' => 'Calendar',
        'last_name' => 'Target',
        'full_name' => 'Calendar Target',
    ]);

    $otherPatient = $this->createPatient([
        'first_name' => 'Other',
        'last_name' => 'Patient',
        'full_name' => 'Other Patient',
    ]);

    $date = now()->addDay()->toDateString();

    Appointment::query()->create([
        'appointment_no' => 'APT-TARGET-001',
        'patient_id' => $targetPatient->id,
        'specialty_id' => $this->specialty->id,
        'service_id' => $this->service->id,
        'assigned_doctor_id' => $this->doctor->id,
        'appointment_date' => $date,
        'start_time' => '09:00',
        'end_time' => '09:30',
        'status' => 'pending',
        'booking_source' => BookingSource::WEB_APP->value,
    ]);

    Appointment::query()->create([
        'appointment_no' => 'APT-OTHER-001',
        'patient_id' => $otherPatient->id,
        'specialty_id' => $this->specialty->id,
        'service_id' => $this->service->id,
        'assigned_doctor_id' => $this->doctor->id,
        'appointment_date' => $date,
        'start_time' => '10:00',
        'end_time' => '10:30',
        'status' => 'pending',
        'booking_source' => BookingSource::WEB_APP->value,
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.appointments.index', [
            'month' => now()->addDay()->format('Y-m'),
            'search' => 'Calendar',
            'status' => 'pending',
            'date' => $date,
            'specialty_id' => $this->specialty->id,
        ]))
        ->assertOk()
        ->assertSee('Calendar Target')
        ->assertDontSee('Other Patient')
        ->assertSee(route('admin.patients.show', $targetPatient->id), false);
});

it('changes appointment status to checked in and creates a linked visit from appointment data', function () {
    $appointment = Appointment::query()->create([
        'appointment_no' => 'APT-CHECKIN-001',
        'patient_id' => $this->patient->id,
        'specialty_id' => $this->specialty->id,
        'service_id' => $this->service->id,
        'assigned_doctor_id' => $this->doctor->id,
        'appointment_date' => now()->addDay()->toDateString(),
        'start_time' => '11:00',
        'end_time' => '11:30',
        'status' => AppointmentStatus::PENDING,
        'booking_source' => BookingSource::WEB_APP->value,
    ]);

    $this->actingAs($this->admin)
        ->from(route('admin.appointments.index'))
        ->post(route('admin.appointments.status.update', $appointment), [
            'status' => AppointmentStatus::CHECKED_IN->value,
            'notes' => 'Arrived at reception',
        ])
        ->assertRedirect(route('admin.appointments.index'));

    $this->assertDatabaseHas('appointments', [
        'id' => $appointment->id,
        'status' => AppointmentStatus::CHECKED_IN->value,
    ]);

    $this->assertDatabaseHas('visits', [
        'appointment_id' => $appointment->id,
        'patient_id' => $appointment->patient_id,
        'doctor_id' => $appointment->assigned_doctor_id,
        'status' => 'checked_in',
    ]);

    $this->actingAs($this->admin)
        ->post(route('admin.appointments.status.update', $appointment), [
            'status' => AppointmentStatus::CHECKED_IN->value,
        ])
        ->assertRedirect();

    expect(Visit::query()->where('appointment_id', $appointment->id)->count())->toBe(1);
});

it('locks appointment after confirmation and prevents further quick status updates', function () {
    $appointment = Appointment::query()->create([
        'appointment_no' => 'APT-CONFIRM-LOCK-001',
        'patient_id' => $this->patient->id,
        'specialty_id' => $this->specialty->id,
        'service_id' => $this->service->id,
        'assigned_doctor_id' => $this->doctor->id,
        'appointment_date' => now()->addDay()->toDateString(),
        'start_time' => '12:00',
        'end_time' => '12:30',
        'status' => AppointmentStatus::PENDING,
        'booking_source' => BookingSource::WEB_APP->value,
    ]);

    $this->actingAs($this->admin)
        ->post(route('admin.appointments.status.update', $appointment), [
            'status' => AppointmentStatus::CONFIRMED->value,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('appointments', [
        'id' => $appointment->id,
        'status' => AppointmentStatus::CONFIRMED->value,
    ]);

    $this->assertDatabaseHas('visits', [
        'appointment_id' => $appointment->id,
        'patient_id' => $appointment->patient_id,
        'doctor_id' => $appointment->assigned_doctor_id,
        'status' => 'checked_in',
    ]);

    $confirmedVisit = Visit::query()->where('appointment_id', $appointment->id)->first();
    expect($confirmedVisit)->not->toBeNull();
    expect($confirmedVisit->visit_date?->toDateString())->toBe(now()->toDateString());

    $this->actingAs($this->admin)
        ->post(route('admin.appointments.status.update', $appointment), [
            'status' => AppointmentStatus::COMPLETED->value,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('appointments', [
        'id' => $appointment->id,
        'status' => AppointmentStatus::CONFIRMED->value,
    ]);

    expect(Visit::query()->where('appointment_id', $appointment->id)->count())->toBe(1);
});

