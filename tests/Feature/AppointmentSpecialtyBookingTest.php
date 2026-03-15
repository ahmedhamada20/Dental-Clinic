<?php

use App\Models\Appointment\Appointment;
use App\Models\Clinic\MedicalSpecialty;
use App\Models\Clinic\Service;
use App\Models\Clinic\ServiceCategory;
use App\Models\Patient\Patient;
use App\Models\User;
use App\Modules\Appointments\Actions\BookAppointmentAction;
use App\Modules\Appointments\DTOs\BookAppointmentData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

function createSpecialty(string $name): MedicalSpecialty
{
    return MedicalSpecialty::query()->create([
        'name' => $name,
        'description' => $name . ' specialty',
        'is_active' => true,
    ]);
}

function createServiceForSpecialty(MedicalSpecialty $specialty, string $name): Service
{
    $category = ServiceCategory::query()->create([
        'medical_specialty_id' => $specialty->id,
        'name_ar' => 'خدمة ' . $name,
        'name_en' => 'Category ' . $name . ' ' . Str::random(4),
        'is_active' => true,
        'sort_order' => 1,
    ]);

    return Service::query()->create([
        'category_id' => $category->id,
        'code' => 'SRV-' . strtoupper(Str::random(6)),
        'name_ar' => 'خدمة ' . $name,
        'name_en' => $name,
        'description_ar' => null,
        'description_en' => null,
        'default_price' => 100,
        'duration_minutes' => 30,
        'is_bookable' => true,
        'is_active' => true,
        'sort_order' => 1,
    ]);
}

function createDentistForSpecialty(MedicalSpecialty $specialty, string $suffix): User
{
    return User::query()->create([
        'first_name' => 'Doc',
        'last_name' => $suffix,
        'full_name' => 'Doc ' . $suffix,
        'email' => 'doc-' . Str::lower($suffix) . '@example.com',
        'phone' => '0100000' . rand(1000, 9999),
        'password' => bcrypt('password'),
        'user_type' => 'doctor',
        'specialty_id' => $specialty->id,
        'status' => 'active',
    ]);
}

it('books an appointment with matching specialty doctor and service', function () {
    $specialty = createSpecialty('Orthodontics');
    $service = createServiceForSpecialty($specialty, 'Braces Consultation');
    $doctor = createDentistForSpecialty($specialty, 'Ortho');
    $patient = Patient::factory()->create();

    $appointment = app(BookAppointmentAction::class)(new BookAppointmentData(
        patient_id: $patient->id,
        doctor_id: $doctor->id,
        specialty_id: $specialty->id,
        service_id: $service->id,
        appointment_date: now()->addDay()->format('Y-m-d'),
        appointment_time: '10:00',
        notes: 'Initial booking',
    ));

    expect($appointment)->toBeInstanceOf(Appointment::class)
        ->and($appointment->specialty_id)->toBe($specialty->id)
        ->and($appointment->assigned_doctor_id)->toBe($doctor->id)
        ->and($appointment->service_id)->toBe($service->id)
        ->and($appointment->start_time)->toStartWith('10:00');
});

it('rejects booking when doctor does not belong to selected specialty', function () {
    $selectedSpecialty = createSpecialty('Pediatric Dentistry');
    $otherSpecialty = createSpecialty('Endodontics');
    $service = createServiceForSpecialty($selectedSpecialty, 'Child Checkup');
    $doctor = createDentistForSpecialty($otherSpecialty, 'Endo');
    $patient = Patient::factory()->create();

    $action = fn () => app(BookAppointmentAction::class)(new BookAppointmentData(
        patient_id: $patient->id,
        doctor_id: $doctor->id,
        specialty_id: $selectedSpecialty->id,
        service_id: $service->id,
        appointment_date: now()->addDay()->format('Y-m-d'),
        appointment_time: '11:00',
        notes: null,
    ));

    expect($action)->toThrow(InvalidArgumentException::class, 'Selected doctor must belong to the chosen specialty.');
});

it('rejects booking when service does not belong to selected specialty', function () {
    $selectedSpecialty = createSpecialty('Periodontics');
    $otherSpecialty = createSpecialty('Prosthodontics');
    $service = createServiceForSpecialty($otherSpecialty, 'Crown Fitting');
    $doctor = createDentistForSpecialty($selectedSpecialty, 'Perio');
    $patient = Patient::factory()->create();

    $action = fn () => app(BookAppointmentAction::class)(new BookAppointmentData(
        patient_id: $patient->id,
        doctor_id: $doctor->id,
        specialty_id: $selectedSpecialty->id,
        service_id: $service->id,
        appointment_date: now()->addDay()->format('Y-m-d'),
        appointment_time: '12:00',
        notes: null,
    ));

    expect($action)->toThrow(InvalidArgumentException::class, 'Selected service must belong to the chosen specialty.');
});

