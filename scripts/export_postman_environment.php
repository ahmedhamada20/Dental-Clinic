<?php

declare(strict_types=1);

use App\Models\Appointment\Appointment;
use App\Models\Appointment\WaitingListRequest;
use App\Models\Billing\Invoice;
use App\Models\Billing\InvoiceItem;
use App\Models\Billing\Payment;
use App\Models\Billing\Promotion;
use App\Models\Clinic\Service;
use App\Models\Medical\MedicalFile;
use App\Models\Medical\Prescription;
use App\Models\Medical\PrescriptionItem;
use App\Models\Medical\TreatmentPlan;
use App\Models\Medical\TreatmentPlanItem;
use App\Models\Patient\Patient;
use App\Models\User;
use App\Models\Visit\Visit;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$root = dirname(__DIR__);
$outputPath = $root . '/postman/Dental Clinic Local.postman_environment.json';
$sampleFilePath = $root . '/postman/sample-medical-file.txt';
$timestamp = now()->format('YmdHis');

$admin = User::query()
    ->where('user_type', 'admin')
    ->where('status', 'active')
    ->firstOrFail();

$admin->tokens()->where('name', 'like', 'postman-admin-%')->delete();
$adminToken = $admin->createToken('postman-admin-' . Str::uuid())->plainTextToken;

$patient = Patient::query()
    ->where('status', 'active')
    ->withCount([
        'appointments',
        'waitingListRequests',
        'visits',
        'treatmentPlans',
        'prescriptions',
        'medicalFiles',
        'invoices',
        'payments',
    ])
    ->get()
    ->sortByDesc(function (Patient $patient): int {
        return (int) (
            $patient->appointments_count
            + $patient->waiting_list_requests_count
            + $patient->visits_count
            + $patient->treatment_plans_count
            + $patient->prescriptions_count
            + $patient->medical_files_count
            + $patient->invoices_count
            + $patient->payments_count
        );
    })
    ->first();

if (! $patient) {
    throw new RuntimeException('No active patient found. Run database seeders first.');
}

$patient->tokens()->where('name', 'like', 'postman-patient-%')->delete();
$patientToken = $patient->createToken('postman-patient-' . Str::uuid())->plainTextToken;

$doctor = User::query()
    ->where('user_type', 'doctor')
    ->where('status', 'active')
    ->orderBy('id')
    ->firstOrFail();

$service = Service::query()
    ->with('category')
    ->where('is_active', true)
    ->where('is_bookable', true)
    ->orderBy('id')
    ->first();

if (! $service) {
    $service = Service::query()->with('category')->orderBy('id')->first();
}

if (! $service) {
    throw new RuntimeException('No service found. Run database seeders first.');
}

$specialtyId = $service->category?->medical_specialty_id ?? $doctor->specialty_id ?? 1;

$appointment = Appointment::query()
    ->where('patient_id', $patient->id)
    ->orderBy('id')
    ->first() ?? Appointment::query()->orderBy('id')->first();

$waitingList = WaitingListRequest::query()
    ->where('patient_id', $patient->id)
    ->orderBy('id')
    ->first() ?? WaitingListRequest::query()->orderBy('id')->first();

$visit = Visit::query()
    ->where('patient_id', $patient->id)
    ->orderBy('id')
    ->first() ?? Visit::query()->orderBy('id')->first();

$treatmentPlan = TreatmentPlan::query()
    ->where('patient_id', $patient->id)
    ->orderBy('id')
    ->first() ?? TreatmentPlan::query()->orderBy('id')->first();

$treatmentPlanItem = TreatmentPlanItem::query()->orderBy('id')->first();
$prescription = Prescription::query()
    ->where('patient_id', $patient->id)
    ->orderBy('id')
    ->first() ?? Prescription::query()->orderBy('id')->first();
$prescriptionItem = PrescriptionItem::query()->orderBy('id')->first();
$medicalFile = MedicalFile::query()
    ->where('patient_id', $patient->id)
    ->orderBy('id')
    ->first() ?? MedicalFile::query()->orderBy('id')->first();
$invoice = Invoice::query()
    ->where('patient_id', $patient->id)
    ->orderBy('id')
    ->first() ?? Invoice::query()->orderBy('id')->first();
$invoiceItemId = (string) (InvoiceItem::query()->value('id') ?? 1);
$payment = Payment::query()
    ->where('patient_id', $patient->id)
    ->orderBy('id')
    ->first() ?? Payment::query()->orderBy('id')->first();
$promotion = Promotion::query()->orderBy('id')->first();

 $deviceTokenId = (string) (
    Schema::hasTable('device_tokens')
        ? (DB::table('device_tokens')->where('patient_id', $patient->id)->value('id') ?? DB::table('device_tokens')->value('id') ?? '1')
        : '1'
 );

$patientNotificationId = (string) (
    Schema::hasTable('system_notifications')
        ? (DB::table('system_notifications')
            ->where('notifiable_type', Patient::class)
            ->where('notifiable_id', $patient->id)
            ->value('id') ?? DB::table('system_notifications')->value('id') ?? '1')
        : '1'
);
$adminNotificationId = (string) (Schema::hasTable('system_notifications') ? (DB::table('system_notifications')->value('id') ?? '1') : '1');
$notificationLogId = (string) (Schema::hasTable('notification_logs') ? (DB::table('notification_logs')->value('id') ?? '1') : '1');
$auditLogId = (string) (Schema::hasTable('audit_logs') ? (DB::table('audit_logs')->value('id') ?? '1') : '1');
$holidayId = (string) (Schema::hasTable('holidays') ? (DB::table('holidays')->value('id') ?? '1') : '1');
$workingHourId = (string) (Schema::hasTable('working_hours') ? (DB::table('working_hours')->value('id') ?? '1') : '1');

$environment = [
    'id' => (string) Str::uuid(),
    'name' => 'Dental Clinic Local',
    'values' => [
        ['key' => 'base_url', 'value' => 'http://127.0.0.1:8000', 'type' => 'default', 'enabled' => true],
        ['key' => 'admin_email', 'value' => (string) $admin->email, 'type' => 'default', 'enabled' => true],
        ['key' => 'admin_password', 'value' => 'password', 'type' => 'secret', 'enabled' => true],
        ['key' => 'admin_token', 'value' => $adminToken, 'type' => 'secret', 'enabled' => true],
        ['key' => 'patient_phone', 'value' => (string) $patient->phone, 'type' => 'default', 'enabled' => true],
        ['key' => 'patient_password', 'value' => 'patient123', 'type' => 'secret', 'enabled' => true],
        ['key' => 'patient_token', 'value' => $patientToken, 'type' => 'secret', 'enabled' => true],
        ['key' => 'patient_id', 'value' => (string) $patient->id, 'type' => 'default', 'enabled' => true],
        ['key' => 'doctor_id', 'value' => (string) $doctor->id, 'type' => 'default', 'enabled' => true],
        ['key' => 'specialty_id', 'value' => (string) $specialtyId, 'type' => 'default', 'enabled' => true],
        ['key' => 'service_id', 'value' => (string) $service->id, 'type' => 'default', 'enabled' => true],
        ['key' => 'appointment_id', 'value' => (string) ($appointment?->id ?? 1), 'type' => 'default', 'enabled' => true],
        ['key' => 'waiting_list_request_id', 'value' => (string) ($waitingList?->id ?? 1), 'type' => 'default', 'enabled' => true],
        ['key' => 'visit_id', 'value' => (string) ($visit?->id ?? 1), 'type' => 'default', 'enabled' => true],
        ['key' => 'treatment_plan_id', 'value' => (string) ($treatmentPlan?->id ?? 1), 'type' => 'default', 'enabled' => true],
        ['key' => 'treatment_plan_item_id', 'value' => (string) ($treatmentPlanItem?->id ?? 1), 'type' => 'default', 'enabled' => true],
        ['key' => 'prescription_id', 'value' => (string) ($prescription?->id ?? 1), 'type' => 'default', 'enabled' => true],
        ['key' => 'prescription_item_id', 'value' => (string) ($prescriptionItem?->id ?? 1), 'type' => 'default', 'enabled' => true],
        ['key' => 'medical_file_id', 'value' => (string) ($medicalFile?->id ?? 1), 'type' => 'default', 'enabled' => true],
        ['key' => 'invoice_id', 'value' => (string) ($invoice?->id ?? 1), 'type' => 'default', 'enabled' => true],
        ['key' => 'invoice_item_id', 'value' => $invoiceItemId, 'type' => 'default', 'enabled' => true],
        ['key' => 'payment_id', 'value' => (string) ($payment?->id ?? 1), 'type' => 'default', 'enabled' => true],
        ['key' => 'promotion_id', 'value' => (string) ($promotion?->id ?? 1), 'type' => 'default', 'enabled' => true],
        ['key' => 'device_token_id', 'value' => $deviceTokenId, 'type' => 'default', 'enabled' => true],
        ['key' => 'patient_notification_id', 'value' => $patientNotificationId, 'type' => 'default', 'enabled' => true],
        ['key' => 'admin_notification_id', 'value' => $adminNotificationId, 'type' => 'default', 'enabled' => true],
        ['key' => 'notification_log_id', 'value' => $notificationLogId, 'type' => 'default', 'enabled' => true],
        ['key' => 'audit_log_id', 'value' => $auditLogId, 'type' => 'default', 'enabled' => true],
        ['key' => 'holiday_id', 'value' => $holidayId, 'type' => 'default', 'enabled' => true],
        ['key' => 'working_hour_id', 'value' => $workingHourId, 'type' => 'default', 'enabled' => true],
        ['key' => 'report_type', 'value' => 'patients', 'type' => 'default', 'enabled' => true],
        ['key' => 'from_date', 'value' => now()->subDays(30)->toDateString(), 'type' => 'default', 'enabled' => true],
        ['key' => 'to_date', 'value' => now()->toDateString(), 'type' => 'default', 'enabled' => true],
        ['key' => 'appointment_date', 'value' => now()->addDays(2)->toDateString(), 'type' => 'default', 'enabled' => true],
        ['key' => 'appointment_time', 'value' => '10:00', 'type' => 'default', 'enabled' => true],
        ['key' => 'new_patient_first_name', 'value' => 'Postman', 'type' => 'default', 'enabled' => true],
        ['key' => 'new_patient_last_name', 'value' => 'Tester', 'type' => 'default', 'enabled' => true],
        ['key' => 'new_patient_email', 'value' => 'postman.' . $timestamp . '@example.com', 'type' => 'default', 'enabled' => true],
        ['key' => 'new_patient_phone', 'value' => '010' . substr($timestamp, -8), 'type' => 'default', 'enabled' => true],
        ['key' => 'new_patient_password', 'value' => 'patient123', 'type' => 'secret', 'enabled' => true],
        ['key' => 'new_promotion_code', 'value' => 'POSTMAN-' . substr($timestamp, -6), 'type' => 'default', 'enabled' => true],
        ['key' => 'created_admin_patient_id', 'value' => '', 'type' => 'default', 'enabled' => true],
        ['key' => 'created_promotion_id', 'value' => '', 'type' => 'default', 'enabled' => true],
        ['key' => 'sample_file_path', 'value' => $sampleFilePath, 'type' => 'default', 'enabled' => true],
    ],
    '_postman_variable_scope' => 'environment',
    '_postman_exported_at' => now()->toIso8601String(),
    '_postman_exported_using' => 'Laravel helper script',
];

file_put_contents(
    $outputPath,
    json_encode($environment, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
);

echo "Environment exported to {$outputPath}" . PHP_EOL;

