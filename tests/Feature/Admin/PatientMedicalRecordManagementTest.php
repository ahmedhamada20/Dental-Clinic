<?php

use App\Enums\AppointmentStatus;
use App\Enums\PatientStatus;
use App\Models\Appointment\Appointment;
use App\Models\Billing\Invoice;
use App\Models\Medical\MedicalFile;
use App\Models\Medical\Prescription;
use App\Models\Patient\EmergencyContact;
use App\Models\Patient\Patient;
use App\Models\Patient\PatientMedicalHistory;
use App\Models\Patient\PatientProfile;
use App\Models\User;
use App\Models\Visit\Visit;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Gate::define('manage_patients', fn (User $user) => true);

    Storage::fake('public');

    $this->user = User::query()->create([
        'first_name' => 'Admin',
        'last_name' => 'User',
        'full_name' => 'Admin User',
        'email' => 'admin@example.com',
        'phone' => '5550001',
        'password' => Hash::make('password'),
        'user_type' => 'admin',
        'status' => 'active',
    ]);
});

it('allows dashboard users to create and review a full patient medical record', function () {
    $response = $this->actingAs($this->user)->post(route('admin.patients.store'), [
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'phone' => '5551111',
        'email' => 'jane@example.com',
        'gender' => 'female',
        'date_of_birth' => '1992-02-10',
        'address' => '123 Smile Street',
        'city' => 'Tabuk',
        'alternate_phone' => '5552222',
        'notes' => 'Requires follow-up reminders.',
        'status' => PatientStatus::ACTIVE->value,
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
        'profile' => [
            'occupation' => 'Teacher',
            'marital_status' => 'Married',
            'preferred_language' => 'English',
            'blood_group' => 'A+',
            'notes' => 'Prefers evening appointments',
        ],
        'medical_history' => [
            'allergies' => 'Penicillin',
            'chronic_diseases' => 'Asthma',
            'current_medications' => 'Inhaler',
            'medical_notes' => 'Monitor vitals',
            'dental_history' => 'Root canal in 2022',
            'important_alerts' => 'Needs antibiotic alternatives',
        ],
        'emergency_contacts' => [
            ['name' => 'John Doe', 'relation' => 'Spouse', 'phone' => '5553333', 'notes' => 'Primary contact'],
            ['name' => 'Mia Doe', 'relation' => 'Sister', 'phone' => '5554444', 'notes' => 'Backup contact'],
        ],
        'new_file' => UploadedFile::fake()->create('medical-summary.pdf', 32, 'application/pdf'),
        'new_file_title' => 'Initial Medical Summary',
        'new_file_category' => 'other',
        'new_file_notes' => 'Uploaded during onboarding',
        'new_file_visible_to_patient' => '1',
    ]);

    $patient = Patient::query()->where('email', 'jane@example.com')->firstOrFail();

    $response->assertRedirect(route('admin.patients.show', $patient));

    expect($patient->profile)->not->toBeNull()
        ->and($patient->profile->occupation)->toBe('Teacher')
        ->and($patient->medicalHistory)->not->toBeNull()
        ->and($patient->medicalHistory->allergies)->toBe('Penicillin')
        ->and($patient->emergencyContacts()->count())->toBe(2)
        ->and($patient->medicalFiles()->count())->toBe(1);

    Storage::disk('public')->assertExists($patient->medicalFiles()->first()->file_path);

    $showResponse = $this->actingAs($this->user)->get(route('admin.patients.show', ['patient' => $patient->id, 'tab' => 'overview']));
    $showResponse->assertOk()
        ->assertSee('Jane Doe')
        ->assertSee('Penicillin')
        ->assertSee('John Doe')
        ->assertSee('Initial Medical Summary');
});

it('supports updating medical history, timeline review, emergency contacts, and medical files from the dashboard', function () {
    $patient = Patient::query()->create([
        'first_name' => 'Mark',
        'last_name' => 'Stone',
        'full_name' => 'Mark Stone',
        'phone' => '5559000',
        'email' => 'mark@example.com',
        'gender' => 'male',
        'date_of_birth' => '1988-01-05',
        'age' => 37,
        'status' => PatientStatus::ACTIVE,
        'registered_from' => 'dashboard',
    ]);

    PatientProfile::query()->create([
        'patient_id' => $patient->id,
        'occupation' => 'Engineer',
    ]);

    PatientMedicalHistory::query()->create([
        'patient_id' => $patient->id,
        'allergies' => 'Dust',
    ]);

    EmergencyContact::query()->create([
        'patient_id' => $patient->id,
        'name' => 'Lara Stone',
        'relation' => 'Wife',
        'phone' => '5559010',
    ]);

    Appointment::query()->create([
        'patient_id' => $patient->id,
        'appointment_no' => 'APT-1001',
        'appointment_date' => now()->subDays(4),
        'status' => AppointmentStatus::CONFIRMED,
    ]);

    $visit = Visit::query()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $this->user->id,
        'visit_no' => 'VIS-1001',
        'visit_date' => now()->subDays(3),
        'status' => 'completed',
        'chief_complaint' => 'Tooth pain',
    ]);

    Prescription::query()->create([
        'patient_id' => $patient->id,
        'visit_id' => $visit->id,
        'doctor_id' => $this->user->id,
        'notes' => 'Take after meals',
        'issued_at' => now()->subDays(2),
    ]);

    Invoice::query()->create([
        'patient_id' => $patient->id,
        'visit_id' => $visit->id,
        'created_by' => $this->user->id,
        'invoice_no' => 'INV-1001',
        'subtotal' => 100,
        'total' => 100,
        'paid_amount' => 40,
        'remaining_amount' => 60,
        'status' => 'partial_paid',
        'issued_at' => now()->subDay(),
    ]);

    MedicalFile::query()->create([
        'patient_id' => $patient->id,
        'uploaded_by' => $this->user->id,
        'file_category' => 'other',
        'title' => 'Existing X-Ray',
        'file_path' => 'medical-files/test/existing.pdf',
        'file_name' => 'existing.pdf',
        'file_extension' => 'pdf',
        'mime_type' => 'application/pdf',
        'file_size' => 200,
        'is_visible_to_patient' => true,
        'uploaded_at' => now(),
    ]);

    $this->actingAs($this->user)->put(route('admin.patients.update', $patient), [
        'first_name' => 'Mark',
        'last_name' => 'Stone',
        'phone' => '5559000',
        'email' => 'mark@example.com',
        'gender' => 'male',
        'date_of_birth' => '1988-01-05',
        'address' => 'Updated address',
        'city' => 'Riyadh',
        'alternate_phone' => '5559090',
        'notes' => 'Updated patient note',
        'status' => PatientStatus::ACTIVE->value,
        'profile' => [
            'occupation' => 'Senior Engineer',
            'marital_status' => 'Married',
            'preferred_language' => 'Arabic',
            'blood_group' => 'B+',
            'notes' => 'Priority follow-up',
        ],
        'medical_history' => [
            'allergies' => 'Dust, Latex',
            'chronic_diseases' => 'Hypertension',
            'current_medications' => 'Medication A',
            'medical_notes' => 'Recheck blood pressure',
            'dental_history' => 'Implant completed',
            'important_alerts' => 'Needs careful anesthesia review',
        ],
        'emergency_contacts' => [
            ['name' => 'Lara Stone', 'relation' => 'Wife', 'phone' => '5559010', 'notes' => 'Primary'],
            ['name' => 'Omar Stone', 'relation' => 'Brother', 'phone' => '5559020', 'notes' => 'Secondary'],
        ],
    ])->assertRedirect(route('admin.patients.show', $patient));

    $this->actingAs($this->user)->post(route('admin.patients.medical-history.store', $patient), [
        'allergies' => 'Dust, Latex',
        'chronic_diseases' => 'Hypertension',
        'current_medications' => 'Medication A',
        'medical_notes' => 'Reviewed again',
        'dental_history' => 'Implant completed',
        'important_alerts' => 'Critical allergy note',
    ])->assertRedirect(route('admin.patients.show', ['patient' => $patient->id, 'tab' => 'history']));

    $contact = $patient->fresh()->emergencyContacts()->first();

    $this->actingAs($this->user)->put(route('admin.patients.emergency-contacts.update', [$patient, $contact->id]), [
        'name' => 'Lara Stone',
        'relation' => 'Wife',
        'phone' => '5559011',
        'notes' => 'Updated contact number',
    ])->assertRedirect(route('admin.patients.show', ['patient' => $patient->id, 'tab' => 'contacts']));

    $this->actingAs($this->user)->post(route('admin.patients.medical-files.store', $patient), [
        'file' => UploadedFile::fake()->create('follow-up-report.pdf', 24, 'application/pdf'),
        'title' => 'Follow-up Report',
        'file_category' => 'other',
        'notes' => 'Added after review',
        'is_visible_to_patient' => '1',
    ])->assertRedirect(route('admin.patients.show', ['patient' => $patient->id, 'tab' => 'files']));

    $timelineResponse = $this->actingAs($this->user)->get(route('admin.patients.show', ['patient' => $patient->id, 'tab' => 'timeline']));

    $timelineResponse->assertOk()
        ->assertSee('APT-1001')
        ->assertSee('VIS-1001')
        ->assertSee('Prescription #')
        ->assertSee('INV-1001')
        ->assertSee('Follow-up Report');

    expect($patient->fresh()->profile->occupation)->toBe('Senior Engineer')
        ->and($patient->fresh()->medicalHistory->important_alerts)->toBe('Critical allergy note')
        ->and($patient->fresh()->emergencyContacts()->count())->toBe(2)
        ->and($patient->fresh()->medicalFiles()->count())->toBe(2);
});

