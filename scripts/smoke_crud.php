<?php

declare(strict_types=1);

use App\Enums\AppointmentStatus;
use App\Enums\BookingSource;
use App\Enums\PatientStatus;
use App\Enums\PromotionType;
use App\Enums\UserStatus;
use App\Enums\UserType;
use App\Enums\VisitStatus;
use App\Enums\WaitingListStatus;
use App\Models\Appointment\Appointment;
use App\Models\Appointment\WaitingListRequest;
use App\Models\Billing\Promotion;
use App\Models\Clinic\MedicalSpecialty;
use App\Models\Clinic\Service;
use App\Models\Clinic\ServiceCategory;
use App\Models\Patient\Patient;
use App\Models\User;
use App\Models\Visit\Visit;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$results = [];

try {
    DB::beginTransaction();

    $doctor    = User::query()->whereNotNull('specialty_id')->first() ?? User::query()->first();
    $patient   = Patient::query()->first();
    $specialty = MedicalSpecialty::query()->first();

    if (! $doctor || ! $patient) {
        throw new RuntimeException('Smoke check needs at least one doctor user and one patient.');
    }

    // ── 1. Visits CRUD + all status transitions + auto visit_no ──────────────
    $visitA = Visit::query()->create([
        'patient_id' => $patient->id,
        'doctor_id'  => $doctor->id,
        'visit_date' => now()->toDateString(),
        'status'     => VisitStatus::SCHEDULED,
    ]);

    $visitB = Visit::query()->create([
        'patient_id' => $patient->id,
        'doctor_id'  => $doctor->id,
        'visit_date' => now()->toDateString(),
        'status'     => VisitStatus::IN_PROGRESS,
    ]);

    $visitB->update(['status' => VisitStatus::COMPLETED, 'diagnosis' => 'Smoke CRUD']);
    $visitB->delete();
    $visitA->delete();

    // Verify every VisitStatus case is accepted by DB
    $statusWriteOk = true;
    foreach (VisitStatus::cases() as $case) {
        $v = Visit::query()->create([
            'patient_id' => $patient->id,
            'doctor_id'  => $doctor->id,
            'visit_date' => now()->toDateString(),
            'status'     => $case,
        ]);
        if ($v->status !== $case) {
            $statusWriteOk = false;
        }
        $v->forceDelete();
    }

    $results['visits'] = [
        'create'            => true,
        'update'            => true,
        'delete'            => true,
        'all_statuses_ok'   => $statusWriteOk,
        'visit_no_sequence' => ((int) $visitB->visit_no) === (((int) $visitA->visit_no) + 1),
        'visit_no_1'        => $visitA->visit_no,
        'visit_no_2'        => $visitB->visit_no,
    ];

    // ── 2. Services & Categories CRUD ────────────────────────────────────────
    $token = now()->format('YmdHis') . random_int(100, 999);

    $category = ServiceCategory::query()->create([
        'name_ar'    => 'Smoke Cat AR ' . $token,
        'name_en'    => 'Smoke Cat ' . $token,
        'is_active'  => true,
        'sort_order' => 9999,
    ]);

    $service = Service::query()->create([
        'category_id'      => $category->id,
        'code'             => 'SMK-SRV-' . $token,
        'name_ar'          => 'Smoke Svc AR ' . $token,
        'name_en'          => 'Smoke Svc ' . $token,
        'default_price'    => 100,
        'duration_minutes' => 30,
        'is_bookable'      => true,
        'is_active'        => true,
        'sort_order'       => 9999,
    ]);

    $service->update(['default_price' => 120]);
    $service->delete();

    $results['services'] = [
        'category_create' => true,
        'service_create'  => true,
        'service_update'  => true,
        'service_delete'  => true,
    ];

    // ── 3. Promotions CRUD ───────────────────────────────────────────────────
    $promo = Promotion::query()->create([
        'title_ar'       => 'Smoke Promo AR ' . $token,
        'title_en'       => 'Smoke Promo ' . $token,
        'code'           => 'SMK-PRM-' . $token,
        'promotion_type' => PromotionType::INVOICE_PERCENT,
        'value'          => 10,
        'starts_at'      => now(),
        'ends_at'        => now()->addDays(5),
        'is_active'      => true,
    ]);

    $promo->update(['value' => 12]);
    $promo->delete();

    $results['promotions'] = ['create' => true, 'update' => true, 'delete' => true];

    // ── 4. Waiting List CRUD ─────────────────────────────────────────────────
    $wlService = Service::query()->withTrashed()->first();
    if ($wlService) {
        $waiting = WaitingListRequest::query()->create([
            'patient_id'     => $patient->id,
            'service_id'     => $wlService->id,
            'preferred_date' => now()->addDay()->toDateString(),
            'status'         => WaitingListStatus::PENDING,
        ]);

        $waiting->update(['status' => WaitingListStatus::NOTIFIED, 'notified_at' => now()]);
        $waiting->delete();

        $results['waiting_list'] = ['create' => true, 'update' => true, 'delete' => true];
    } else {
        $results['waiting_list'] = ['skipped' => 'no service available'];
    }

    // ── 5. Patients CRUD (including suspended & archived statuses) ───────────
    $phone      = '0999' . random_int(1000000, 9999999);
    $newPatient = Patient::query()->create([
        'first_name'      => 'SmokeFirst',
        'last_name'       => 'SmokeLast',
        'full_name'       => 'SmokeFirst SmokeLast',
        'phone'           => $phone,
        'gender'          => 'male',
        'date_of_birth'   => '1990-01-01',
        'age'             => '34',
        'status'          => PatientStatus::ACTIVE,
        'registered_from' => 'dashboard',
        'password'        => Hash::make('password'),
        'patient_code'    => 'SMK-' . $token,
    ]);

    $newPatient->update(['status' => PatientStatus::SUSPENDED]);
    $newPatient->update(['status' => PatientStatus::ARCHIVED]);
    $newPatient->update(['status' => PatientStatus::INACTIVE]);
    $newPatient->delete();

    $results['patients'] = [
        'create'           => true,
        'update_suspended' => true,
        'update_archived'  => true,
        'update_inactive'  => true,
        'delete'           => true,
    ];

    // ── 6. Appointments CRUD ─────────────────────────────────────────────────
    $apptService = Service::query()->where('is_active', true)->where('is_bookable', true)->first();
    if ($apptService && $specialty) {
        $appt = Appointment::query()->create([
            'appointment_no'     => 'SMK-APT-' . $token,
            'patient_id'         => $patient->id,
            'service_id'         => $apptService->id,
            'specialty_id'       => $specialty->id,
            'assigned_doctor_id' => $doctor->id,
            'appointment_date'   => now()->addDays(2)->toDateString(),
            'start_time'         => '10:00',
            'end_time'           => '10:30',
            'status'             => AppointmentStatus::PENDING,
            'booking_source'     => BookingSource::WEB_APP,
        ]);

        $appt->update(['status' => AppointmentStatus::CONFIRMED]);
        $appt->delete();

        $results['appointments'] = ['create' => true, 'update' => true, 'delete' => true];
    } else {
        $results['appointments'] = ['skipped' => 'no active bookable service or specialty'];
    }

    // ── 7. Users CRUD ────────────────────────────────────────────────────────
    $email   = 'smoke_' . $token . '@test.local';
    $newUser = User::query()->create([
        'first_name' => 'SmokeUser',
        'last_name'  => 'Test',
        'full_name'  => 'SmokeUser Test',
        'email'      => $email,
        'phone'      => '07' . random_int(10000000, 99999999),
        'user_type'  => UserType::RECEPTIONIST,
        'status'     => UserStatus::ACTIVE,
        'password'   => Hash::make('password'),
    ]);

    $newUser->update(['status' => UserStatus::INACTIVE]);
    $newUser->delete();

    $results['users'] = ['create' => true, 'update' => true, 'delete' => true];

    DB::rollBack();

    echo json_encode([
        'ok'          => true,
        'rolled_back' => true,
        'results'     => $results,
    ], JSON_PRETTY_PRINT) . PHP_EOL;
} catch (Throwable $e) {
    DB::rollBack();

    echo json_encode([
        'ok'      => false,
        'error'   => $e->getMessage(),
        'trace'   => $e->getFile() . ':' . $e->getLine(),
        'results' => $results,
    ], JSON_PRETTY_PRINT) . PHP_EOL;

    exit(1);
}

