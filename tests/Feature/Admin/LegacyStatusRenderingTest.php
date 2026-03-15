<?php

use App\Models\Appointment\VisitTicket;
use App\Models\Medical\TreatmentPlan;
use App\Models\Patient\Patient;
use App\Models\User;
use App\Models\Visit\Visit;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    foreach ([
        'admin.dashboard.index',
        'admin.appointments.index',
        'admin.waiting-list.index',
        'admin.patients.index',
        'admin.visits.index',
        'admin.service-categories.index',
        'admin.services.index',
        'admin.prescriptions.index',
        'admin.billing.index',
        'admin.promotions.index',
        'admin.reports.index',
        'admin.settings.index',
        'admin.users.index',
        'admin.roles.index',
        'admin.notifications.index',
    ] as $index => $name) {
        Route::get('/__legacy-status-test/' . ($index + 1), fn () => 'ok')->name($name);
    }

    $this->admin = User::query()->create([
        'first_name' => 'Admin',
        'last_name' => 'User',
        'full_name' => 'Admin User',
        'email' => 'legacy-status-admin@example.com',
        'phone' => '500300100',
        'password' => Hash::make('password'),
        'user_type' => 'admin',
        'status' => 'active',
    ]);

    $this->dentist = User::query()->create([
        'first_name' => 'Nora',
        'last_name' => 'Doctor',
        'full_name' => 'Nora Doctor',
        'email' => 'legacy-status-dentist@example.com',
        'phone' => '500300101',
        'password' => Hash::make('password'),
        'user_type' => 'admin',
        'status' => 'active',
    ]);

    $this->patient = Patient::query()->create([
        'patient_code' => 'PAT-7001',
        'first_name' => 'Salma',
        'last_name' => 'Care',
        'full_name' => 'Salma Care',
        'phone' => '500300200',
        'email' => 'salma.care@example.com',
        'password' => Hash::make('secret123'),
        'gender' => 'female',
        'date_of_birth' => '1992-05-12',
        'age' => 33,
        'address' => '45 Clinic Avenue',
        'city' => 'Cairo',
        'status' => 'active',
        'registered_from' => 'dashboard',
    ]);

    $this->visit = Visit::query()->create([
        'visit_no' => 'VIS-LEGACY-1001',
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->dentist->id,
        'checked_in_by' => $this->admin->id,
        'visit_date' => now()->toDateString(),
        'status' => 'completed',
        'chief_complaint' => 'Follow-up pain review',
        'diagnosis' => 'Post-treatment review',
        'clinical_notes' => 'Legacy status compatibility check',
        'internal_notes' => 'Render test',
    ]);
});

it('renders the visit details page when the stored ticket status uses a legacy database value', function () {
    VisitTicket::query()->create([
        'ticket_date' => now()->toDateString(),
        'ticket_number' => 1001,
        'visit_id' => $this->visit->id,
        'patient_id' => $this->patient->id,
        'status' => 'waiting',
    ]);

    $response = $this->actingAs($this->admin)->get(route('admin.visits.show', $this->visit));

    $response->assertOk()
        ->assertSee('Visit Details')
        ->assertSee('1001')
        ->assertSee('Issued');
});

it('renders the treatment plans index with enum-backed statuses safely', function () {
    TreatmentPlan::query()->create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->dentist->id,
        'visit_id' => $this->visit->id,
        'title' => 'Restorative plan',
        'description' => 'Phased restorative care.',
        'estimated_total' => 1250,
        'status' => 'draft',
    ]);

    $response = $this->actingAs($this->admin)->get(route('admin.treatment-plans.index'));

    $response->assertOk()
        ->assertSee('Restorative plan')
        ->assertSee('Draft');
});

it('renders the treatment plan details page with enum labels instead of string casting the enum object', function () {
    $plan = TreatmentPlan::query()->create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->dentist->id,
        'visit_id' => $this->visit->id,
        'title' => 'Implant treatment plan',
        'description' => 'Multi-stage implant workup.',
        'estimated_total' => 3200,
        'status' => 'draft',
    ]);

    $response = $this->actingAs($this->admin)->get(route('admin.treatment-plans.show', $plan));

    $response->assertOk()
        ->assertSee('Implant treatment plan')
        ->assertSee('Draft');
});

