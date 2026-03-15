<?php

use App\Models\Appointment\Appointment;
use App\Models\Billing\Invoice;
use App\Models\Billing\InvoiceItem;
use App\Models\Billing\Payment;
use App\Models\Clinic\Service;
use App\Models\Patient\Patient;
use App\Models\User;
use App\Modules\Reports\Controllers\Admin\ReportController;
use App\Modules\Reports\Requests\ReportFilterRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function createReportRequest(array $query = []): ReportFilterRequest {
    $request = ReportFilterRequest::create('/admin/reports/doctors', 'GET', $query);

    $user = User::query()->create([
        'first_name' => 'Admin',
        'last_name' => 'User',
        'full_name' => 'Admin User',
        'email' => 'admin@example.com',
        'phone' => '01000000001',
        'password' => bcrypt('password'),
        'user_type' => 'admin',
        'status' => 'active',
    ]);

    $request->setUserResolver(fn () => $user);

    return $request;
}

it('generates advanced doctor, service, patient, revenue, and invoice analytics', function () {
    $doctor = User::query()->create([
        'first_name' => 'Sara',
        'last_name' => 'Dent',
        'full_name' => 'Sara Dent',
        'email' => 'doctor@example.com',
        'phone' => '01000000002',
        'password' => bcrypt('password'),
        'user_type' => 'doctor',
        'status' => 'active',
    ]);

    $patient = Patient::query()->create([
        'patient_code' => 'PAT-001',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'full_name' => 'John Doe',
        'phone' => '01000000003',
        'email' => 'patient@example.com',
        'password' => bcrypt('password'),
        'gender' => 'male',
        'date_of_birth' => '1990-01-01',
        'age' => 35,
        'address' => 'Test Address',
        'city' => 'Cairo',
        'status' => 'active',
        'registered_from' => 'dashboard',
    ]);

    $service = Service::query()->create([
        'category_id' => 1,
        'code' => 'SRV-001',
        'name_ar' => 'تنظيف',
        'name_en' => 'Cleaning',
        'default_price' => 100,
        'duration_minutes' => 30,
        'is_bookable' => true,
        'is_active' => true,
    ]);

    $appointment = Appointment::query()->create([
        'appointment_no' => 'APT-001',
        'patient_id' => $patient->id,
        'service_id' => $service->id,
        'assigned_doctor_id' => $doctor->id,
        'appointment_date' => now()->toDateString(),
        'start_time' => '10:00:00',
        'end_time' => '10:30:00',
        'status' => 'completed',
        'booking_source' => 'dashboard',
    ]);

    $visitId = DB::table('visits')->insertGetId([
        'visit_no' => 'VIS-001',
        'appointment_id' => $appointment->id,
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'visit_date' => now()->toDateString(),
        'status' => 'completed',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $invoice = Invoice::query()->create([
        'invoice_no' => 'INV-001',
        'patient_id' => $patient->id,
        'visit_id' => $visitId,
        'created_by' => $doctor->id,
        'subtotal' => 100,
        'discount_value' => 0,
        'discount_amount' => 0,
        'total' => 100,
        'paid_amount' => 60,
        'remaining_amount' => 40,
        'status' => 'partial_paid',
        'issued_at' => now(),
    ]);

    InvoiceItem::query()->create([
        'invoice_id' => $invoice->id,
        'service_id' => $service->id,
        'item_type' => 'service',
        'item_name_ar' => 'تنظيف',
        'item_name_en' => 'Cleaning',
        'quantity' => 1,
        'unit_price' => 100,
        'discount_amount' => 0,
        'total' => 100,
    ]);

    Payment::query()->create([
        'payment_no' => 'PAY-001',
        'patient_id' => $patient->id,
        'invoice_id' => $invoice->id,
        'received_by' => $doctor->id,
        'payment_method' => 'cash',
        'amount' => 60,
        'payment_date' => now(),
    ]);

    $controller = app(ReportController::class);
    $request = createReportRequest(['from_date' => now()->subDay()->toDateString(), 'to_date' => now()->addDay()->toDateString()]);

    $doctorPayload = $controller->doctors($request)->getData(true);
    expect(data_get($doctorPayload, 'data.summary.total_doctors'))->toBe(1);
    expect(data_get($doctorPayload, 'data.rows.0.doctor_name'))->toBe('Sara Dent');

    $servicePayload = $controller->services($request)->getData(true);
    expect(data_get($servicePayload, 'data.rows.0.service_name'))->toBe('Cleaning');

    $patientPayload = $controller->patients($request)->getData(true);
    expect(data_get($patientPayload, 'data.summary.patients_with_appointments'))->toBe(1);

    $revenuePayload = $controller->revenue($request)->getData(true);
    expect(data_get($revenuePayload, 'data.summary.total_revenue_collected'))->toBe(60.0);

    $invoicePayload = $controller->invoices($request)->getData(true);
    expect(data_get($invoicePayload, 'data.summary.unpaid_invoices_count'))->toBe(1);
});

it('exports a report to pdf and excel', function () {
    $controller = app(ReportController::class);
    $request = createReportRequest();

    $pdfResponse = $controller->exportPdf($request, 'doctors');
    expect($pdfResponse->headers->get('content-type'))->toContain('application/pdf');

    $excelResponse = $controller->exportExcel($request, 'doctors');
    expect($excelResponse->headers->get('content-disposition'))->toContain('.xlsx');
});
