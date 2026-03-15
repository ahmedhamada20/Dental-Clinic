<?php

use App\Models\Appointment\Appointment;
use App\Models\Clinic\MedicalSpecialty;
use App\Models\Clinic\Service;
use App\Models\Clinic\ServiceCategory;
use App\Models\Patient\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    Gate::define('manage_users', fn (User $user) => true);
});

function specialtyAdmin(): User
{
    return User::query()->create([
        'first_name' => 'Admin',
        'last_name' => 'Specialty',
        'full_name' => 'Admin Specialty',
        'email' => 'admin+' . Str::lower(Str::random(6)) . '@example.com',
        'phone' => '555' . random_int(1000000, 9999999),
        'password' => Hash::make('password'),
        'user_type' => 'admin',
        'status' => 'active',
    ]);
}

function specialtyDoctor(MedicalSpecialty $specialty, string $suffix): User
{
    return User::query()->create([
        'first_name' => 'Doc',
        'last_name' => $suffix,
        'full_name' => 'Doc ' . $suffix,
        'email' => 'doc-' . Str::lower($suffix) . '-' . Str::lower(Str::random(4)) . '@example.com',
        'phone' => '556' . random_int(1000000, 9999999),
        'password' => Hash::make('password'),
        'user_type' => 'doctor',
        'specialty_id' => $specialty->id,
        'status' => 'active',
    ]);
}

it('allows admin to create edit and activate/deactivate specialties', function () {
    $admin = specialtyAdmin();

    $createResponse = $this->actingAs($admin)->post(route('admin.specialties.store'), [
        'name' => 'Implantology',
        'description' => 'Implant specialty',
        'is_active' => true,
    ]);

    $specialty = MedicalSpecialty::query()->where('name', 'Implantology')->first();

    $createResponse->assertRedirect(route('admin.specialties.index'));
    expect($specialty)->not->toBeNull();

    $updateResponse = $this->actingAs($admin)->put(route('admin.specialties.update', $specialty), [
        'name' => 'Advanced Implantology',
        'description' => 'Updated',
        'is_active' => true,
    ]);

    $updateResponse->assertRedirect(route('admin.specialties.index'));
    expect($specialty->fresh()->name)->toBe('Advanced Implantology');

    $deactivateResponse = $this->actingAs($admin)->patch(route('admin.specialties.deactivate', $specialty));
    $deactivateResponse->assertRedirect();
    expect($specialty->fresh()->is_active)->toBeFalse();

    $activateResponse = $this->actingAs($admin)->patch(route('admin.specialties.activate', $specialty));
    $activateResponse->assertRedirect();
    expect($specialty->fresh()->is_active)->toBeTrue();
});

it('filters doctors by specialty on users index', function () {
    $admin = specialtyAdmin();
    $endo = MedicalSpecialty::query()->create(['name' => 'Endodontics', 'description' => null, 'is_active' => true]);
    $peds = MedicalSpecialty::query()->create(['name' => 'Pediatrics', 'description' => null, 'is_active' => true]);

    $endoDoctor = specialtyDoctor($endo, 'Endo');
    $pedsDoctor = specialtyDoctor($peds, 'Peds');

    $response = $this->actingAs($admin)
        ->get(route('admin.users.index', ['specialty_id' => $endo->id, 'user_type' => 'doctor']));

    $response->assertOk();
    $response->assertSee($endoDoctor->display_name);
    $response->assertDontSee($pedsDoctor->display_name);
});

it('filters appointments by specialty on appointments index', function () {
    $admin = specialtyAdmin();

    $specialtyA = MedicalSpecialty::query()->create(['name' => 'Orthodontics', 'description' => null, 'is_active' => true]);
    $specialtyB = MedicalSpecialty::query()->create(['name' => 'Oral Surgery', 'description' => null, 'is_active' => true]);

    $categoryA = ServiceCategory::query()->create([
        'medical_specialty_id' => $specialtyA->id,
        'name_ar' => 'تقويم',
        'name_en' => 'Ortho Category',
        'is_active' => true,
        'sort_order' => 1,
    ]);
    $categoryB = ServiceCategory::query()->create([
        'medical_specialty_id' => $specialtyB->id,
        'name_ar' => 'جراحة',
        'name_en' => 'Surgery Category',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    $serviceA = Service::query()->create([
        'category_id' => $categoryA->id,
        'name_ar' => 'خدمة أ',
        'name_en' => 'Service A',
        'default_price' => 100,
        'duration_minutes' => 30,
        'is_bookable' => true,
        'is_active' => true,
        'sort_order' => 1,
    ]);
    $serviceB = Service::query()->create([
        'category_id' => $categoryB->id,
        'name_ar' => 'خدمة ب',
        'name_en' => 'Service B',
        'default_price' => 100,
        'duration_minutes' => 30,
        'is_bookable' => true,
        'is_active' => true,
        'sort_order' => 1,
    ]);

    $doctorA = specialtyDoctor($specialtyA, 'A');
    $doctorB = specialtyDoctor($specialtyB, 'B');
    $patient = Patient::factory()->create();

    $aptA = Appointment::query()->create([
        'appointment_no' => 'APT-A-' . Str::upper(Str::random(6)),
        'patient_id' => $patient->id,
        'specialty_id' => $specialtyA->id,
        'service_id' => $serviceA->id,
        'assigned_doctor_id' => $doctorA->id,
        'appointment_date' => now()->toDateString(),
        'start_time' => '09:00',
        'end_time' => '09:30',
        'status' => 'pending',
    ]);

    $aptB = Appointment::query()->create([
        'appointment_no' => 'APT-B-' . Str::upper(Str::random(6)),
        'patient_id' => $patient->id,
        'specialty_id' => $specialtyB->id,
        'service_id' => $serviceB->id,
        'assigned_doctor_id' => $doctorB->id,
        'appointment_date' => now()->toDateString(),
        'start_time' => '10:00',
        'end_time' => '10:30',
        'status' => 'pending',
    ]);

    $response = $this->actingAs($admin)->get(route('admin.appointments.index', ['specialty_id' => $specialtyA->id]));

    $response->assertOk();
    $response->assertSee($aptA->appointment_no);
    $response->assertDontSee($aptB->appointment_no);
});

