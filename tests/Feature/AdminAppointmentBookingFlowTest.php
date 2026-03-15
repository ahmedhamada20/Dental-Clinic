<?php

use App\Models\Appointment\Appointment;
use App\Models\Clinic\MedicalSpecialty;
use App\Models\Clinic\Service;
use App\Models\Clinic\ServiceCategory;
use App\Models\Patient\Patient;
use App\Models\System\AuditLog;
use App\Models\User;
use App\Modules\Audit\Services\AuditLogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    Gate::define('manage_patients', fn (User $user) => true);

    $this->mock(AuditLogService::class)
        ->shouldReceive('log')
        ->andReturn(new AuditLog());
});

function createAdminUser(): User
{
    return User::query()->create([
        'first_name' => 'Admin',
        'last_name' => 'User',
        'full_name' => 'Admin User',
        'email' => 'admin+' . Str::lower(Str::random(6)) . '@example.com',
        'phone' => '555' . random_int(1000000, 9999999),
        'password' => Hash::make('password'),
        'user_type' => 'admin',
        'status' => 'active',
    ]);
}

function adminCreateSpecialty(string $name): MedicalSpecialty
{
    return MedicalSpecialty::query()->create([
        'name' => $name,
        'description' => $name . ' specialty',
        'is_active' => true,
    ]);
}

function adminCreateServiceForSpecialty(MedicalSpecialty $specialty, string $name): Service
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
        'default_price' => 150,
        'duration_minutes' => 45,
        'is_bookable' => true,
        'is_active' => true,
        'sort_order' => 1,
    ]);
}

function adminCreateDoctorForSpecialty(MedicalSpecialty $specialty, string $suffix): User
{
    return User::query()->create([
        'first_name' => 'Doc',
        'last_name' => $suffix,
        'full_name' => 'Doc ' . $suffix,
        'email' => 'doctor-' . Str::lower($suffix) . '-' . Str::lower(Str::random(4)) . '@example.com',
        'phone' => '555' . random_int(1000000, 9999999),
        'password' => Hash::make('password'),
        'user_type' => 'doctor',
        'specialty_id' => $specialty->id,
        'status' => 'active',
    ]);
}

it('creates an appointment through the admin flow with matching specialty doctor and service', function () {
    $admin = createAdminUser();
    $specialty = adminCreateSpecialty('Implantology');
    $service = adminCreateServiceForSpecialty($specialty, 'Implant Consultation');
    $doctor = adminCreateDoctorForSpecialty($specialty, 'Implant');
    $patient = Patient::factory()->create();

    $response = $this->actingAs($admin)->post(route('admin.appointments.store'), [
        'patient_id' => $patient->id,
        'specialty_id' => $specialty->id,
        'doctor_id' => $doctor->id,
        'service_id' => $service->id,
        'appointment_date' => now()->addDay()->format('Y-m-d'),
        'appointment_time' => '09:30',
        'status' => 'pending',
        'notes' => 'Reception booking',
    ]);

    $appointment = Appointment::query()->orderByDesc('id')->first();

    $response->assertRedirect(route('admin.appointments.show', $appointment));

    expect($appointment)
        ->not->toBeNull()
        ->and($appointment->patient_id)->toBe($patient->id)
        ->and($appointment->doctor_id)->toBe($doctor->id)
        ->and($appointment->specialty_id)->toBe($specialty->id)
        ->and($appointment->service_id)->toBe($service->id)
        ->and($appointment->appointment_time)->toBe('09:30:00');
});

it('rejects admin booking when doctor specialty does not match', function () {
    $admin = createAdminUser();
    $selectedSpecialty = adminCreateSpecialty('Oral Surgery');
    $otherSpecialty = adminCreateSpecialty('Orthodontics');
    $service = adminCreateServiceForSpecialty($selectedSpecialty, 'Extraction Consultation');
    $doctor = adminCreateDoctorForSpecialty($otherSpecialty, 'Mismatch');
    $patient = Patient::factory()->create();

    $response = $this->actingAs($admin)
        ->from(route('admin.appointments.create'))
        ->post(route('admin.appointments.store'), [
            'patient_id' => $patient->id,
            'specialty_id' => $selectedSpecialty->id,
            'doctor_id' => $doctor->id,
            'service_id' => $service->id,
            'appointment_date' => now()->addDay()->format('Y-m-d'),
            'appointment_time' => '11:00',
            'status' => 'pending',
        ]);

    $response->assertRedirect(route('admin.appointments.create'));
    $response->assertSessionHasErrors('doctor_id');
});

it('shows only specialty doctors and services after selecting a specialty in the booking form', function () {
    $admin = createAdminUser();
    $selectedSpecialty = adminCreateSpecialty('Endodontics');
    $otherSpecialty = adminCreateSpecialty('Pediatric Dentistry');

    $selectedDoctor = adminCreateDoctorForSpecialty($selectedSpecialty, 'Selected');
    $otherDoctor = adminCreateDoctorForSpecialty($otherSpecialty, 'Other');
    $selectedService = adminCreateServiceForSpecialty($selectedSpecialty, 'Root Canal');
    $otherService = adminCreateServiceForSpecialty($otherSpecialty, 'Kids Cleaning');

    $response = $this->actingAs($admin)->get(route('admin.appointments.create', [
        'specialty_id' => $selectedSpecialty->id,
    ]));

    $response->assertOk();
    $response->assertSee($selectedDoctor->display_name);
    $response->assertDontSee($otherDoctor->display_name);
    $response->assertSee($selectedService->name_en);
    $response->assertDontSee($otherService->name_en);
});
