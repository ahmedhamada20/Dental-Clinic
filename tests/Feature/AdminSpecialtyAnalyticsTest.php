<?php

use App\Models\Appointment\Appointment;
use App\Models\Billing\Invoice;
use App\Models\Billing\InvoiceItem;
use App\Models\Clinic\MedicalSpecialty;
use App\Models\Clinic\Service;
use App\Models\Clinic\ServiceCategory;
use App\Models\Patient\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

function analyticsAdminUser(): User
{
    return User::query()->create([
        'first_name' => 'Admin',
        'last_name' => 'Analytics',
        'full_name' => 'Admin Analytics',
        'email' => 'admin.analytics+' . Str::lower(Str::random(5)) . '@example.com',
        'phone' => '557' . random_int(1000000, 9999999),
        'password' => Hash::make('password'),
        'user_type' => 'admin',
        'status' => 'active',
    ]);
}

function seedSpecialtyAnalyticsDataset(): array
{
    $specialtyA = MedicalSpecialty::query()->create([
        'name' => 'Orthodontics',
        'description' => null,
        'is_active' => true,
    ]);

    $specialtyB = MedicalSpecialty::query()->create([
        'name' => 'Endodontics',
        'description' => null,
        'is_active' => true,
    ]);

    $categoryA = ServiceCategory::query()->create([
        'medical_specialty_id' => $specialtyA->id,
        'name_ar' => 'تقويم',
        'name_en' => 'Ortho Category',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    $categoryB = ServiceCategory::query()->create([
        'medical_specialty_id' => $specialtyB->id,
        'name_ar' => 'علاج عصب',
        'name_en' => 'Endo Category',
        'is_active' => true,
        'sort_order' => 2,
    ]);

    $serviceA = Service::query()->create([
        'category_id' => $categoryA->id,
        'code' => 'SVC-ORTHO-' . Str::upper(Str::random(4)),
        'name_ar' => 'استشارة تقويم',
        'name_en' => 'Ortho Consultation',
        'default_price' => 250,
        'duration_minutes' => 45,
        'is_bookable' => true,
        'is_active' => true,
        'sort_order' => 1,
    ]);

    $serviceB = Service::query()->create([
        'category_id' => $categoryB->id,
        'code' => 'SVC-ENDO-' . Str::upper(Str::random(4)),
        'name_ar' => 'استشارة عصب',
        'name_en' => 'Endo Consultation',
        'default_price' => 300,
        'duration_minutes' => 60,
        'is_bookable' => true,
        'is_active' => true,
        'sort_order' => 2,
    ]);

    $doctorA = User::query()->create([
        'first_name' => 'Doc',
        'last_name' => 'Ortho',
        'full_name' => 'Doc Ortho',
        'email' => 'doc.ortho.' . Str::lower(Str::random(5)) . '@example.com',
        'phone' => '558' . random_int(1000000, 9999999),
        'password' => Hash::make('password'),
        'user_type' => 'doctor',
        'specialty_id' => $specialtyA->id,
        'status' => 'active',
    ]);

    $doctorB = User::query()->create([
        'first_name' => 'Doc',
        'last_name' => 'Endo',
        'full_name' => 'Doc Endo',
        'email' => 'doc.endo.' . Str::lower(Str::random(5)) . '@example.com',
        'phone' => '559' . random_int(1000000, 9999999),
        'password' => Hash::make('password'),
        'user_type' => 'doctor',
        'specialty_id' => $specialtyB->id,
        'status' => 'active',
    ]);

    $patient = Patient::factory()->create();

    Appointment::query()->create([
        'appointment_no' => 'APT-ORTHO-' . Str::upper(Str::random(4)),
        'patient_id' => $patient->id,
        'specialty_id' => $specialtyA->id,
        'service_id' => $serviceA->id,
        'assigned_doctor_id' => $doctorA->id,
        'appointment_date' => now()->toDateString(),
        'start_time' => '09:00',
        'end_time' => '09:45',
        'status' => 'completed',
    ]);

    Appointment::query()->create([
        'appointment_no' => 'APT-ENDO-' . Str::upper(Str::random(4)),
        'patient_id' => $patient->id,
        'specialty_id' => $specialtyB->id,
        'service_id' => $serviceB->id,
        'assigned_doctor_id' => $doctorB->id,
        'appointment_date' => now()->toDateString(),
        'start_time' => '10:00',
        'end_time' => '11:00',
        'status' => 'pending',
    ]);

    $invoice = Invoice::query()->create([
        'invoice_no' => 'INV-SPC-' . Str::upper(Str::random(5)),
        'patient_id' => $patient->id,
        'visit_id' => null,
        'created_by' => $doctorA->id,
        'subtotal' => 250,
        'discount_value' => 0,
        'discount_amount' => 0,
        'total' => 250,
        'paid_amount' => 250,
        'remaining_amount' => 0,
        'status' => 'paid',
        'issued_at' => now(),
    ]);

    InvoiceItem::query()->create([
        'invoice_id' => $invoice->id,
        'service_id' => $serviceA->id,
        'item_type' => 'service',
        'item_name_ar' => 'استشارة تقويم',
        'item_name_en' => 'Ortho Consultation',
        'quantity' => 1,
        'unit_price' => 250,
        'discount_amount' => 0,
        'total' => 250,
    ]);

    return [$specialtyA, $specialtyB];
}

it('shows specialty-aware widgets in admin reports', function () {
    $admin = analyticsAdminUser();
    [$specialtyA, $specialtyB] = seedSpecialtyAnalyticsDataset();

    $response = $this->actingAs($admin)->get(route('admin.reports.index', [
        'from_date' => now()->subDay()->toDateString(),
        'to_date' => now()->addDay()->toDateString(),
    ]));

    $response->assertOk();
    $response->assertSee('Appointments by Specialty');
    $response->assertSee('Doctors by Specialty');
    $response->assertSee('Revenue by Specialty');
    $response->assertSee('Daily Workload by Specialty');
    $response->assertSee($specialtyA->name);
    $response->assertSee($specialtyB->name);
});

it('shows specialty-aware widgets on admin dashboard', function () {
    $admin = analyticsAdminUser();
    [$specialtyA, $specialtyB] = seedSpecialtyAnalyticsDataset();

    $response = $this->actingAs($admin)->get(route('admin.dashboard.index'));

    $response->assertOk();
    $response->assertSee('Appointments by Specialty');
    $response->assertSee('Doctors by Specialty');
    $response->assertSee('Revenue by Specialty');
    $response->assertSee('Daily Workload by Specialty');
    $response->assertSee($specialtyA->name);
    $response->assertSee($specialtyB->name);
});

