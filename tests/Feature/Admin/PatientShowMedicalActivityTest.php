<?php

use App\Enums\AppointmentStatus;
use App\Enums\VisitStatus;
use App\Models\Appointment\Appointment;
use App\Models\Medical\Prescription;
use App\Models\Medical\PrescriptionItem;
use App\Models\Visit\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Support\AdminFeatureTestHelpers;

uses(RefreshDatabase::class, AdminFeatureTestHelpers::class);

beforeEach(function () {
    $this->seedAdminFeaturePermissions();

    $this->admin = $this->createAdminUser([
        'patients.view',
        'appointments.view',
        'visits.view',
        'prescriptions.view',
    ]);

    [$this->specialty, $this->category, $this->service] = $this->createSpecialtyCategoryService();
    $this->doctor = $this->createDentist($this->specialty);
});

it('shows patient appointments, multi-visit history, and prescription print links on patient page', function () {
    $patient = $this->createPatient(['full_name' => 'Target Patient']);
    $otherPatient = $this->createPatient(['full_name' => 'Other Patient']);

    $appointmentOne = Appointment::query()->create([
        'appointment_no' => 'APT-' . strtoupper(Str::random(8)),
        'patient_id' => $patient->id,
        'service_id' => $this->service->id,
        'assigned_doctor_id' => $this->doctor->id,
        'appointment_date' => now()->toDateString(),
        'start_time' => '10:00:00',
        'end_time' => '10:30:00',
        'status' => AppointmentStatus::COMPLETED,
        'booking_source' => 'dashboard',
    ]);

    $appointmentTwo = Appointment::query()->create([
        'appointment_no' => 'APT-' . strtoupper(Str::random(8)),
        'patient_id' => $patient->id,
        'service_id' => $this->service->id,
        'assigned_doctor_id' => $this->doctor->id,
        'appointment_date' => now()->addDay()->toDateString(),
        'start_time' => '11:00:00',
        'end_time' => '11:30:00',
        'status' => AppointmentStatus::COMPLETED,
        'booking_source' => 'dashboard',
    ]);

    $otherAppointment = Appointment::query()->create([
        'appointment_no' => 'APT-' . strtoupper(Str::random(8)),
        'patient_id' => $otherPatient->id,
        'service_id' => $this->service->id,
        'assigned_doctor_id' => $this->doctor->id,
        'appointment_date' => now()->toDateString(),
        'start_time' => '12:00:00',
        'end_time' => '12:30:00',
        'status' => AppointmentStatus::COMPLETED,
        'booking_source' => 'dashboard',
    ]);

    $visitOne = Visit::query()->create([
        'visit_no' => 'VST-' . strtoupper(Str::random(8)),
        'appointment_id' => $appointmentOne->id,
        'patient_id' => $patient->id,
        'doctor_id' => $this->doctor->id,
        'visit_date' => now()->toDateString(),
        'status' => VisitStatus::COMPLETED,
    ]);

    $visitTwo = Visit::query()->create([
        'visit_no' => 'VST-' . strtoupper(Str::random(8)),
        'appointment_id' => $appointmentTwo->id,
        'patient_id' => $patient->id,
        'doctor_id' => $this->doctor->id,
        'visit_date' => now()->addDay()->toDateString(),
        'status' => VisitStatus::COMPLETED,
    ]);

    Visit::query()->create([
        'visit_no' => 'VST-' . strtoupper(Str::random(8)),
        'appointment_id' => $otherAppointment->id,
        'patient_id' => $otherPatient->id,
        'doctor_id' => $this->doctor->id,
        'visit_date' => now()->toDateString(),
        'status' => VisitStatus::COMPLETED,
    ]);

    $prescriptionOne = Prescription::query()->create([
        'patient_id' => $patient->id,
        'visit_id' => $visitOne->id,
        'doctor_id' => $this->doctor->id,
        'notes' => 'First visit prescription',
        'issued_at' => now(),
    ]);

    $prescriptionTwo = Prescription::query()->create([
        'patient_id' => $patient->id,
        'visit_id' => $visitTwo->id,
        'doctor_id' => $this->doctor->id,
        'notes' => 'Second visit prescription',
        'issued_at' => now()->addDay(),
    ]);

    PrescriptionItem::query()->create([
        'prescription_id' => $prescriptionOne->id,
        'medicine_name' => 'Amoxicillin',
    ]);

    PrescriptionItem::query()->create([
        'prescription_id' => $prescriptionTwo->id,
        'medicine_name' => 'Ibuprofen',
    ]);

    $response = $this->actingAs($this->admin)
        ->get(route('admin.patients.show', ['patient' => $patient->id, 'tab' => 'overview']))
        ->assertOk();

    $response
        ->assertSee($appointmentOne->appointment_no)
        ->assertSee($appointmentTwo->appointment_no)
        ->assertSee($visitOne->visit_no)
        ->assertSee($visitTwo->visit_no)
        ->assertSee(route('admin.patients.prescriptions.print', [$patient, $prescriptionOne]), false)
        ->assertSee(route('admin.patients.prescriptions.print', [$patient, $prescriptionTwo]), false)
        ->assertDontSee($otherAppointment->appointment_no);

    $this->actingAs($this->admin)
        ->get(route('admin.patients.prescriptions.show', [$patient, $prescriptionOne]))
        ->assertOk()
        ->assertSee('Amoxicillin');

    $this->actingAs($this->admin)
        ->get(route('admin.patients.prescriptions.print', [$patient, $prescriptionTwo]))
        ->assertOk()
        ->assertSee('Ibuprofen');
});

it('returns not found when opening a prescription with mismatched patient context', function () {
    $patient = $this->createPatient();
    $wrongPatient = $this->createPatient();

    $appointment = Appointment::query()->create([
        'appointment_no' => 'APT-' . strtoupper(Str::random(8)),
        'patient_id' => $patient->id,
        'service_id' => $this->service->id,
        'assigned_doctor_id' => $this->doctor->id,
        'appointment_date' => now()->toDateString(),
        'start_time' => '09:00:00',
        'end_time' => '09:30:00',
        'status' => AppointmentStatus::COMPLETED,
        'booking_source' => 'dashboard',
    ]);

    $visit = Visit::query()->create([
        'visit_no' => 'VST-' . strtoupper(Str::random(8)),
        'appointment_id' => $appointment->id,
        'patient_id' => $patient->id,
        'doctor_id' => $this->doctor->id,
        'visit_date' => now()->toDateString(),
        'status' => VisitStatus::COMPLETED,
    ]);

    $prescription = Prescription::query()->create([
        'patient_id' => $patient->id,
        'visit_id' => $visit->id,
        'doctor_id' => $this->doctor->id,
        'issued_at' => now(),
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.patients.prescriptions.show', [$wrongPatient, $prescription]))
        ->assertNotFound();

    $this->actingAs($this->admin)
        ->get(route('admin.patients.prescriptions.print', [$wrongPatient, $prescription]))
        ->assertNotFound();
});

