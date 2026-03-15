<?php

namespace Database\Seeders;

use App\Enums\AppointmentStatus;
use App\Enums\BookingSource;
use App\Enums\DiscountType;
use App\Enums\FileCategory;
use App\Enums\InvoiceStatus;
use App\Enums\NotificationType;
use App\Enums\PaymentMethod;
use App\Enums\TicketStatus;
use App\Enums\TreatmentPlanItemStatus;
use App\Enums\TreatmentPlanStatus;
use App\Enums\UserStatus;
use App\Enums\UserType;
use App\Enums\VisitStatus;
use App\Models\Appointment\Appointment;
use App\Models\Appointment\AppointmentStatusLog;
use App\Models\Appointment\VisitTicket;
use App\Models\Appointment\WaitingListRequest;
use App\Models\Billing\Invoice;
use App\Models\Billing\InvoiceItem;
use App\Models\Billing\Payment;
use App\Models\Billing\PaymentAllocation;
use App\Models\Billing\Promotion;
use App\Models\Billing\PromotionService;
use App\Models\Clinic\MedicalSpecialty;
use App\Models\Clinic\Service;
use App\Models\Medical\MedicalFile;
use App\Models\Medical\Prescription;
use App\Models\Medical\PrescriptionItem;
use App\Models\Medical\TreatmentPlan;
use App\Models\Medical\TreatmentPlanItem;
use App\Models\Patient\Patient;
use App\Models\System\NotificationLog;
use App\Models\System\SystemNotification;
use App\Models\User;
use App\Models\Visit\Visit;
use App\Models\Visit\VisitNote;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProjectDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedStaffUsers();

        if (Patient::query()->count() < 80) {
            Patient::factory()->count(80 - Patient::query()->count())->create();
        }

        $patients = Patient::query()->inRandomOrder()->take(80)->get();
        $doctors = User::query()->where('user_type', UserType::DOCTOR->value)->get();
        $staff = User::query()->whereIn('user_type', [UserType::ADMIN->value, UserType::RECEPTIONIST->value, UserType::ASSISTANT->value])->get();
        $services = Service::query()->where('is_active', true)->where('is_bookable', true)->get();

        if ($services->isEmpty()) {
            $services = Service::query()->get();
        }

        if ($patients->isEmpty() || $doctors->isEmpty() || $services->isEmpty()) {
            return;
        }

        $appointments = $this->seedAppointments($patients, $doctors, $services);
        $this->seedAppointmentLogs($appointments, $staff->isNotEmpty() ? $staff : $doctors);
        $visits = $this->seedVisits($appointments, $doctors, $staff);
        $this->seedWaitingList($patients, $services);
        $this->seedVisitTickets($visits);
        $this->seedVisitNotes($visits, $doctors);
        $this->seedTreatmentPlans($patients, $doctors, $visits, $services);
        $this->seedPrescriptions($patients, $doctors, $visits);
        $promotions = $this->seedPromotions($services);
        $this->seedMedicalFiles($patients, $visits, $staff->isNotEmpty() ? $staff : $doctors);
        $invoices = $this->seedInvoices($patients, $visits, $promotions, $services, $staff->isNotEmpty() ? $staff : $doctors);
        $this->seedNotifications($patients, $staff->isNotEmpty() ? $staff : $doctors, $appointments, $invoices);
    }

    private function seedStaffUsers(): void
    {
        $specialties = MedicalSpecialty::query()->pluck('id');

        $this->createDemoUsers(UserType::DOCTOR, 12, $specialties);
        $this->createDemoUsers(UserType::RECEPTIONIST, 3, $specialties);
        $this->createDemoUsers(UserType::ASSISTANT, 3, $specialties);
    }

    private function createDemoUsers(UserType $type, int $target, Collection $specialties): void
    {
        $current = User::query()->where('user_type', $type->value)->count();
        $remaining = max(0, $target - $current);

        // Map UserType → Spatie role name
        $roleMap = [
            UserType::DOCTOR->value       => 'doctor',
            UserType::RECEPTIONIST->value => 'receptionist',
            UserType::ASSISTANT->value    => 'assistant',
            UserType::ADMIN->value        => 'admin',
        ];
        $roleName = $roleMap[$type->value] ?? null;

        for ($i = 0; $i < $remaining; $i++) {
            $first = fake()->firstName();
            $last = fake()->lastName();
            $specialtyId = $type === UserType::DOCTOR && $specialties->isNotEmpty()
                ? $specialties->random()
                : null;

            $user = User::query()->create([
                'first_name' => $first,
                'last_name' => $last,
                'full_name' => trim("{$first} {$last}"),
                'email' => strtolower($type->value) . '.' . Str::random(8) . '@demo.local',
                'phone' => fake()->numerify('010########'),
                'password' => 'password',
                'user_type' => $type->value,
                'specialty_id' => $specialtyId,
                'status' => UserStatus::ACTIVE->value,
            ]);

            if ($roleName) {
                $user->assignRole($roleName);
            }
        }
    }

    private function seedAppointments(Collection $patients, Collection $doctors, Collection $services): Collection
    {
        $target = 140;
        $count = Appointment::query()->count();
        $toCreate = max(0, $target - $count);

        for ($i = 0; $i < $toCreate; $i++) {
            $patient = $patients->random();
            $service = $services->random();
            $doctor = $this->pickDoctorForService($doctors, $service) ?? $doctors->random();

            $date = fake()->dateTimeBetween('-30 days', '+45 days');
            $start = fake()->numberBetween(9, 18);
            $minute = fake()->randomElement([0, 15, 30, 45]);
            $startTime = sprintf('%02d:%02d:00', $start, $minute);
            $endTime = sprintf('%02d:%02d:00', min(20, $start + 1), $minute);

            Appointment::query()->create([
                'appointment_no' => 'APT-' . now()->format('Ymd') . '-' . str_pad((string) random_int(1, 99999), 5, '0', STR_PAD_LEFT),
                'patient_id' => $patient->id,
                'specialty_id' => $service->category?->medical_specialty_id,
                'service_id' => $service->id,
                'assigned_doctor_id' => $doctor->id,
                'appointment_date' => $date->format('Y-m-d'),
                'start_time' => $startTime,
                'end_time' => $endTime,
                'status' => fake()->randomElement([
                    AppointmentStatus::PENDING->value,
                    AppointmentStatus::CONFIRMED->value,
                    AppointmentStatus::CHECKED_IN->value,
                    AppointmentStatus::IN_PROGRESS->value,
                    AppointmentStatus::COMPLETED->value,
                    AppointmentStatus::NO_SHOW->value,
                ]),
                'booking_source' => fake()->randomElement([
                    BookingSource::WEB_APP->value,
                    BookingSource::MOBILE_APP->value,
                ]),
                'notes' => fake()->optional()->sentence(),
                'confirmed_at' => fake()->optional(0.4)->dateTimeBetween('-20 days', 'now'),
                'checked_in_at' => fake()->optional(0.3)->dateTimeBetween('-20 days', 'now'),
            ]);
        }

        return Appointment::query()->with(['service.category'])->get();
    }

    private function seedAppointmentLogs(Collection $appointments, Collection $actors): void
    {
        if ($actors->isEmpty()) {
            return;
        }

        $target = 220;
        $toCreate = max(0, $target - AppointmentStatusLog::query()->count());

        for ($i = 0; $i < $toCreate; $i++) {
            $appointment = $appointments->random();
            $newStatus = fake()->randomElement([
                AppointmentStatus::CONFIRMED->value,
                AppointmentStatus::CHECKED_IN->value,
                AppointmentStatus::IN_PROGRESS->value,
                AppointmentStatus::COMPLETED->value,
            ]);
            $actor = $actors->random();

            AppointmentStatusLog::query()->create([
                'appointment_id' => $appointment->id,
                'old_status' => $appointment->status?->value ?? AppointmentStatus::PENDING->value,
                'new_status' => $newStatus,
                'changed_by_type' => 'user',
                'changed_by_id' => $actor->id,
                'notes' => fake()->optional()->sentence(),
            ]);
        }
    }

    private function seedVisits(Collection $appointments, Collection $doctors, Collection $staff): Collection
    {
        $target = 90;
        $toCreate = max(0, $target - Visit::query()->count());

        for ($i = 0; $i < $toCreate; $i++) {
            $appointment = $appointments->random();
            $doctor = $appointment->doctor_id
                ? $doctors->firstWhere('id', $appointment->doctor_id)
                : $doctors->random();
            $checker = $staff->isNotEmpty() ? $staff->random() : $doctor;

            Visit::query()->create([
                'visit_no' => 'VIS-' . now()->format('Ymd') . '-' . str_pad((string) random_int(1, 99999), 5, '0', STR_PAD_LEFT),
                'appointment_id' => $appointment->id,
                'patient_id' => $appointment->patient_id,
                'doctor_id' => $doctor?->id ?? $doctors->random()->id,
                'checked_in_by' => $checker?->id,
                'visit_date' => $appointment->appointment_date,
                'start_at' => now()->subDays(fake()->numberBetween(0, 20))->setTime(fake()->numberBetween(9, 18), fake()->randomElement([0, 30])),
                'end_at' => now()->subDays(fake()->numberBetween(0, 20))->setTime(fake()->numberBetween(10, 20), fake()->randomElement([0, 30])),
                'status' => fake()->randomElement([
                    VisitStatus::COMPLETED->value,
                    VisitStatus::CANCELLED->value,
                ]),
                'chief_complaint' => fake()->sentence(),
                'diagnosis' => fake()->optional()->sentence(),
                'clinical_notes' => fake()->optional()->paragraph(),
                'internal_notes' => fake()->optional()->sentence(),
            ]);
        }

        return Visit::query()->get();
    }

    private function seedWaitingList(Collection $patients, Collection $services): void
    {
        $target = 35;
        $toCreate = max(0, $target - WaitingListRequest::query()->count());

        for ($i = 0; $i < $toCreate; $i++) {
            WaitingListRequest::query()->create([
                'patient_id' => $patients->random()->id,
                'service_id' => $services->random()->id,
                'preferred_date' => fake()->dateTimeBetween('+1 day', '+30 days')->format('Y-m-d'),
                'preferred_from_time' => fake()->randomElement(['09:00:00', '10:30:00', '12:00:00', '15:00:00']),
                'preferred_to_time' => fake()->randomElement(['11:00:00', '13:00:00', '16:00:00', '18:00:00']),
                'status' => fake()->randomElement(['waiting', 'notified', 'booked', 'expired']),
                'notified_at' => fake()->optional(0.5)->dateTimeBetween('-10 days', 'now'),
                'expires_at' => fake()->optional(0.5)->dateTimeBetween('now', '+7 days'),
            ]);
        }
    }

    private function seedVisitTickets(Collection $visits): void
    {
        $target = 80;
        $toCreate = max(0, $target - VisitTicket::query()->count());

        for ($i = 0; $i < $toCreate; $i++) {
            $visit = $visits->random();

            DB::table('visit_tickets')->insert([
                'ticket_date' => $visit->visit_date,
                'ticket_number' => fake()->numberBetween(1, 999),
                'appointment_id' => $visit->appointment_id,
                'visit_id' => $visit->id,
                'patient_id' => $visit->patient_id,
                'status' => fake()->randomElement([
                    'waiting',
                    'called',
                    'with_doctor',
                    'done',
                    'missed',
                ]),
                'called_at' => fake()->optional(0.5)->dateTimeBetween('-10 days', 'now'),
                'finished_at' => fake()->optional(0.4)->dateTimeBetween('-10 days', 'now'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function seedVisitNotes(Collection $visits, Collection $doctors): void
    {
        $target = 180;
        $toCreate = max(0, $target - VisitNote::query()->count());

        for ($i = 0; $i < $toCreate; $i++) {
            $visit = $visits->random();
            $doctor = $doctors->random();

            VisitNote::query()->create([
                'visit_id' => $visit->id,
                'doctor_id' => $doctor->id,
                'patient_id' => $visit->patient_id,
                'diagnosis' => fake()->optional()->sentence(),
                'note' => fake()->paragraph(),
                'treatment_plan' => fake()->optional()->sentence(),
                'follow_up_date' => fake()->optional()->dateTimeBetween('+1 day', '+30 days'),
                'attachments' => null,
                'created_by' => $doctor->id,
                'updated_by' => $doctor->id,
            ]);
        }
    }

    private function seedTreatmentPlans(Collection $patients, Collection $doctors, Collection $visits, Collection $services): void
    {
        $target = 55;
        $toCreate = max(0, $target - TreatmentPlan::query()->count());

        for ($i = 0; $i < $toCreate; $i++) {
            $patient = $patients->random();
            $doctor = $doctors->random();
            $visit = $visits->firstWhere('patient_id', $patient->id);

            $plan = TreatmentPlan::query()->create([
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
                'visit_id' => $visit?->id,
                'title' => 'Treatment Plan #' . Str::upper(Str::random(6)),
                'description' => fake()->optional()->paragraph(),
                'estimated_total' => 0,
                'status' => fake()->randomElement([
                    TreatmentPlanStatus::DRAFT->value,
                    TreatmentPlanStatus::COMPLETED->value,
                    TreatmentPlanStatus::CANCELLED->value,
                ]),
                'start_date' => fake()->optional()->dateTimeBetween('-15 days', '+10 days'),
                'end_date' => fake()->optional()->dateTimeBetween('+11 days', '+45 days'),
            ]);

            $itemsCount = fake()->numberBetween(2, 4);
            $estimatedTotal = 0;
            for ($j = 1; $j <= $itemsCount; $j++) {
                $service = $services->random();
                $cost = (float) fake()->randomFloat(2, 120, 1500);
                $estimatedTotal += $cost;

                TreatmentPlanItem::query()->create([
                    'treatment_plan_id' => $plan->id,
                    'service_id' => $service->id,
                    'tooth_number' => (string) fake()->numberBetween(1, 32),
                    'title' => $service->name_en ?: $service->name_ar,
                    'description' => fake()->optional()->sentence(),
                    'session_no' => $j,
                    'estimated_cost' => $cost,
                    'status' => fake()->randomElement([
                        TreatmentPlanItemStatus::PENDING->value,
                        TreatmentPlanItemStatus::IN_PROGRESS->value,
                        TreatmentPlanItemStatus::COMPLETED->value,
                    ]),
                    'planned_date' => fake()->optional()->dateTimeBetween('-5 days', '+20 days'),
                    'completed_visit_id' => fake()->optional(0.2)->randomElement([$visit?->id]),
                ]);
            }

            $plan->update(['estimated_total' => round($estimatedTotal, 2)]);
        }
    }

    private function seedPrescriptions(Collection $patients, Collection $doctors, Collection $visits): void
    {
        $target = 65;
        $toCreate = max(0, $target - Prescription::query()->count());

        for ($i = 0; $i < $toCreate; $i++) {
            $patient = $patients->random();
            $visit = $visits->firstWhere('patient_id', $patient->id) ?? $visits->random();
            $doctor = $doctors->firstWhere('id', $visit->doctor_id) ?? $doctors->random();

            $prescription = Prescription::query()->create([
                'patient_id' => $patient->id,
                'visit_id' => $visit->id,
                'doctor_id' => $doctor->id,
                'notes' => fake()->optional()->sentence(),
                'issued_at' => fake()->dateTimeBetween('-30 days', 'now'),
            ]);

            $itemsCount = fake()->numberBetween(2, 5);
            for ($j = 0; $j < $itemsCount; $j++) {
                PrescriptionItem::query()->create([
                    'prescription_id' => $prescription->id,
                    'medicine_name' => fake()->randomElement(['Amoxicillin', 'Ibuprofen', 'Metronidazole', 'Paracetamol', 'Mouthwash']),
                    'dosage' => fake()->randomElement(['250 mg', '500 mg', '1 tab', '2 tabs']),
                    'frequency' => fake()->randomElement(['Once daily', 'Twice daily', 'Three times daily', 'Every 8 hours']),
                    'dose_duration' => fake()->randomElement(['5 days', '7 days', '10 days']),
                    'treatment_duration' => fake()->randomElement(['1 week', '2 weeks', '1 month']),
                    'duration' => fake()->randomElement(['7 days', '14 days', '30 days']),
                    'instructions' => fake()->optional()->sentence(),
                ]);
            }
        }
    }

    private function seedMedicalFiles(Collection $patients, Collection $visits, Collection $uploaders): void
    {
        $target = 70;
        $toCreate = max(0, $target - MedicalFile::query()->count());

        for ($i = 0; $i < $toCreate; $i++) {
            $patient = $patients->random();
            $visit = $visits->firstWhere('patient_id', $patient->id);
            $uploader = $uploaders->random();

            MedicalFile::query()->create([
                'patient_id' => $patient->id,
                'visit_id' => $visit?->id,
                'uploaded_by' => $uploader->id,
                'file_category' => fake()->randomElement(array_column(FileCategory::cases(), 'value')),
                'title' => fake()->randomElement(['Panoramic X-Ray', 'Lab Result', 'Before/After Photos', 'Clinical Report']),
                'notes' => fake()->optional()->sentence(),
                'file_path' => 'demo-files/' . Str::random(16) . '.pdf',
                'file_name' => 'demo_' . Str::random(8) . '.pdf',
                'file_extension' => 'pdf',
                'mime_type' => 'application/pdf',
                'file_size' => fake()->numberBetween(12000, 850000),
                'is_visible_to_patient' => fake()->boolean(80),
                'uploaded_at' => fake()->dateTimeBetween('-60 days', 'now'),
            ]);
        }
    }

    private function seedPromotions(Collection $services): Collection
    {
        $target = 8;
        $toCreate = max(0, $target - Promotion::query()->count());

        for ($i = 0; $i < $toCreate; $i++) {
            $promotion = Promotion::query()->create([
                'title_ar' => 'عرض خاص #' . ($i + 1),
                'title_en' => 'Special Offer #' . ($i + 1),
                'code' => 'PROMO' . Str::upper(Str::random(5)),
                'promotion_type' => fake()->randomElement(['invoice_percent', 'invoice_fixed', 'service_percent', 'service_fixed']),
                'value' => fake()->randomFloat(2, 5, 30),
                'applies_once' => fake()->boolean(),
                'starts_at' => now()->subDays(fake()->numberBetween(5, 30)),
                'ends_at' => now()->addDays(fake()->numberBetween(10, 60)),
                'is_active' => true,
                'notes' => fake()->optional()->sentence(),
            ]);

            $attachCount = min($services->count(), fake()->numberBetween(1, 4));
            $selected = $services->random($attachCount);
            foreach ($selected as $service) {
                PromotionService::query()->firstOrCreate([
                    'promotion_id' => $promotion->id,
                    'service_id' => $service->id,
                ]);
            }
        }

        return Promotion::query()->get();
    }

    private function seedInvoices(Collection $patients, Collection $visits, Collection $promotions, Collection $services, Collection $creators): Collection
    {
        $target = 75;
        $toCreate = max(0, $target - Invoice::query()->count());

        for ($i = 0; $i < $toCreate; $i++) {
            $patient = $patients->random();
            $visit = $visits->firstWhere('patient_id', $patient->id);
            $creator = $creators->random();
            $promotion = fake()->optional(0.3)->randomElement($promotions->all());
            $discountType = fake()->optional(0.4)->randomElement([DiscountType::PERCENT->value, DiscountType::FIXED->value]);
            $discountValue = $discountType ? fake()->randomFloat(2, 5, 20) : 0;

            $invoice = Invoice::query()->create([
                'invoice_no' => 'INV-' . now()->format('Ymd') . '-' . str_pad((string) random_int(1, 99999), 5, '0', STR_PAD_LEFT),
                'patient_id' => $patient->id,
                'visit_id' => $visit?->id,
                'created_by' => $creator->id,
                'subtotal' => 0,
                'discount_type' => $discountType,
                'discount_value' => $discountValue,
                'discount_amount' => 0,
                'total' => 0,
                'paid_amount' => 0,
                'remaining_amount' => 0,
                'status' => InvoiceStatus::UNPAID->value,
                'promotion_id' => $promotion?->id,
                'notes' => fake()->optional()->sentence(),
                'issued_at' => fake()->dateTimeBetween('-45 days', 'now'),
            ]);

            $itemsCount = fake()->numberBetween(1, 4);
            $subtotal = 0.0;
            for ($j = 0; $j < $itemsCount; $j++) {
                $service = $services->random();
                $qty = fake()->randomFloat(2, 1, 3);
                $unit = (float) ($service->default_price ?: fake()->randomFloat(2, 120, 900));
                $lineDiscount = fake()->optional(0.2)->randomFloat(2, 0, 25) ?: 0;
                $lineTotal = max(0, round(($qty * $unit) - $lineDiscount, 2));
                $subtotal += $lineTotal;

                InvoiceItem::query()->create([
                    'invoice_id' => $invoice->id,
                    'service_id' => $service->id,
                    'treatment_plan_item_id' => null,
                    'item_type' => 'service',
                    'item_name_ar' => $service->name_ar ?? 'خدمة',
                    'item_name_en' => $service->name_en ?: ($service->name_ar ?? 'Service'),
                    'description' => $service->description_en ?: $service->description_ar,
                    'quantity' => $qty,
                    'unit_price' => $unit,
                    'discount_amount' => $lineDiscount,
                    'total' => $lineTotal,
                    'tooth_number' => fake()->optional(0.4)->numberBetween(1, 32),
                ]);
            }

            $discountAmount = 0.0;
            if ($invoice->discount_type === DiscountType::PERCENT && $invoice->discount_value) {
                $discountAmount = round(($subtotal * (float) $invoice->discount_value) / 100, 2);
            } elseif ($invoice->discount_type === DiscountType::FIXED && $invoice->discount_value) {
                $discountAmount = min($subtotal, (float) $invoice->discount_value);
            }

            $total = max(0, round($subtotal - $discountAmount, 2));
            $paymentMode = fake()->randomElement(['none', 'partial', 'full']);
            $paid = $paymentMode === 'none'
                ? 0.0
                : ($paymentMode === 'full' ? $total : round($total * fake()->randomFloat(2, 0.2, 0.8), 2));
            $remaining = max(0, round($total - $paid, 2));
            $status = $paid <= 0
                ? InvoiceStatus::UNPAID->value
                : ($remaining <= 0 ? InvoiceStatus::PAID->value : InvoiceStatus::PARTIALLY_PAID->value);

            $invoice->update([
                'subtotal' => round($subtotal, 2),
                'discount_amount' => round($discountAmount, 2),
                'total' => $total,
                'paid_amount' => $paid,
                'remaining_amount' => $remaining,
                'status' => $status,
            ]);

            if ($paid > 0) {
                $payment = Payment::query()->create([
                    'payment_no' => 'PAY-' . now()->format('Ymd') . '-' . str_pad((string) random_int(1, 99999), 5, '0', STR_PAD_LEFT),
                    'patient_id' => $patient->id,
                    'invoice_id' => $invoice->id,
                    'received_by' => $creator->id,
                    'payment_method' => fake()->randomElement(array_column(PaymentMethod::cases(), 'value')),
                    'amount' => $paid,
                    'reference_no' => fake()->optional()->bothify('REF-#####'),
                    'payment_date' => fake()->dateTimeBetween('-30 days', 'now'),
                    'notes' => fake()->optional()->sentence(),
                ]);

                PaymentAllocation::query()->create([
                    'payment_id' => $payment->id,
                    'invoice_id' => $invoice->id,
                    'allocated_amount' => $paid,
                ]);
            }
        }

        return Invoice::query()->get();
    }

    private function seedNotifications(Collection $patients, Collection $staff, Collection $appointments, Collection $invoices): void
    {
        $target = 120;
        $toCreate = max(0, $target - SystemNotification::query()->count());

        $types = [
            NotificationType::APPOINTMENT_CONFIRMED->value,
            NotificationType::APPOINTMENT_REMINDER->value,
            NotificationType::APPOINTMENT_CANCELLED->value,
            NotificationType::PAYMENT_RECEIVED->value,
        ];

        for ($i = 0; $i < $toCreate; $i++) {
            $patient = $patients->random();
            $type = fake()->randomElement($types);
            $channel = fake()->randomElement(['push', 'in_app', 'system']);
            $status = fake()->randomElement(['pending', 'sent', 'read', 'failed']);

            $notification = SystemNotification::query()->create([
                'notifiable_type' => Patient::class,
                'notifiable_id' => $patient->id,
                'channel' => $channel,
                'title' => Str::title(str_replace('_', ' ', $type)),
                'body' => fake()->sentence(12),
                'type' => $type,
                'data' => [
                    'appointment_id' => $appointments->random()->id,
                    'invoice_id' => $invoices->random()->id,
                ],
                'sent_at' => $status === 'pending' ? null : fake()->dateTimeBetween('-20 days', 'now'),
                'read_at' => $status === 'read' ? fake()->dateTimeBetween('-10 days', 'now') : null,
                'status' => $status,
            ]);

            NotificationLog::query()->create([
                'system_notification_id' => $notification->id,
                'notifiable_type' => Patient::class,
                'notifiable_id' => $patient->id,
                'channel' => fake()->randomElement(['database', 'email', 'sms', 'push']),
                'notification_type' => fake()->randomElement(['appointment_reminder', 'billing_due', 'custom_announcement']),
                'title' => $notification->title,
                'body' => $notification->body,
                'status' => fake()->randomElement(['pending', 'sent', 'failed', 'delivered']),
                'error_message' => null,
                'meta' => ['source' => 'demo_seed'],
                'sent_at' => fake()->optional(0.8)->dateTimeBetween('-20 days', 'now'),
                'triggered_by' => $staff->isNotEmpty() ? $staff->random()->id : null,
                'triggered_by_type' => fake()->randomElement(['manual', 'scheduled', 'auto']),
            ]);
        }
    }

    private function pickDoctorForService(Collection $doctors, Service $service): ?User
    {
        $specialtyId = $service->category?->medical_specialty_id;
        if (!$specialtyId) {
            return $doctors->random();
        }

        return $doctors->firstWhere('specialty_id', $specialtyId) ?? $doctors->random();
    }
}

