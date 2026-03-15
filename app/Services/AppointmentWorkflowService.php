<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Enums\WaitingListStatus;
use App\Models\Appointment\Appointment;
use App\Models\Appointment\WaitingListRequest;
use App\Modules\Appointments\Services\AppointmentStatusService;
use App\Modules\Audit\Services\AuditLogService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AppointmentWorkflowService
{
    public function __construct(
        private AppointmentStatusService $statusLogService,
        private readonly AuditLogService $auditLogService,
    ) {
    }

    public function confirm(Appointment $appointment, ?int $adminId, ?string $notes = null): Appointment
    {
        return $this->transition($appointment, AppointmentStatus::CONFIRMED, $adminId, $notes, function (Appointment $a) {
            $a->confirmed_at = now();
        });
    }

    public function checkIn(Appointment $appointment, ?int $adminId, ?string $notes = null): Appointment
    {
        return $this->transition($appointment, AppointmentStatus::CHECKED_IN, $adminId, $notes, function (Appointment $a) {
            $a->checked_in_at = now();
        });
    }

    public function markNoShow(Appointment $appointment, ?int $adminId, ?string $notes = null): Appointment
    {
        return $this->transition($appointment, AppointmentStatus::NO_SHOW, $adminId, $notes);
    }

    public function complete(Appointment $appointment, ?int $adminId, ?string $notes = null): Appointment
    {
        return $this->transition($appointment, AppointmentStatus::COMPLETED, $adminId, $notes);
    }

    public function cancel(
        Appointment $appointment,
        ?int $adminId,
        string $reason,
        ?string $notes = null,
        ?int $waitingListRequestId = null,
        ?array $reschedule = null
    ): Appointment {
        return DB::transaction(function () use ($appointment, $adminId, $reason, $notes, $waitingListRequestId, $reschedule) {
            $cancelled = $this->transition(
                $appointment,
                AppointmentStatus::CANCELLED_BY_ADMIN,
                $adminId,
                $notes,
                function (Appointment $a) use ($reason, $adminId) {
                    $a->cancellation_reason = $reason;
                    $a->cancelled_at = now();
                    $a->cancelled_by_type = 'user';
                    $a->cancelled_by_id = $adminId;
                }
            );

            if ($waitingListRequestId) {
                $this->convertWaitingListRequest($cancelled, $waitingListRequestId, $reschedule, $adminId, $notes);
            }

            return $cancelled;
        });
    }

    public function reschedule(Appointment $appointment, ?int $adminId, string $date, string $time, ?string $notes = null): Appointment
    {
        return DB::transaction(function () use ($appointment, $adminId, $date, $time, $notes) {
            $before = $appointment->toArray();
            $durationMinutes = $this->resolveDurationMinutes($appointment->start_time, $appointment->end_time);
            $startAt = CarbonImmutable::parse("{$date} {$time}");

            $appointment->appointment_date = $date;
            $appointment->start_time = $startAt->format('H:i:s');
            $appointment->end_time = $startAt->addMinutes($durationMinutes)->format('H:i:s');

            if (in_array($appointment->status?->value ?? $appointment->status, [
                AppointmentStatus::CANCELLED_BY_ADMIN->value,
                AppointmentStatus::CANCELLED_BY_PATIENT->value,
                AppointmentStatus::NO_SHOW->value,
                AppointmentStatus::COMPLETED->value,
            ], true)) {
                $appointment->status = AppointmentStatus::PENDING;
            }

            $oldStatus = $appointment->getOriginal('status');
            $appointment->save();

            $this->statusLogService->createStatusLog(
                $appointment->id,
                is_object($oldStatus) ? $oldStatus->value : $oldStatus,
                Arr::get($appointment->toArray(), 'status'),
                'user',
                $adminId,
                $notes ?? 'Appointment rescheduled'
            );

            $appointment = $appointment->refresh();

            $this->auditLogService->log('appointments', 'status_change', $appointment, $before, $appointment->toArray());

            return $appointment;
        });
    }

    private function transition(
        Appointment $appointment,
        AppointmentStatus $to,
        ?int $adminId,
        ?string $notes = null,
        ?callable $mutate = null
    ): Appointment {
        return DB::transaction(function () use ($appointment, $to, $adminId, $notes, $mutate) {
            $before = $appointment->toArray();
            $oldStatus = $appointment->status?->value ?? $appointment->status;

            if ($oldStatus === $to->value) {
                return $appointment;
            }

            if ($mutate) {
                $mutate($appointment);
            }

            $appointment->status = $to;
            $appointment->save();

            $this->statusLogService->createStatusLog(
                $appointment->id,
                $oldStatus,
                $to->value,
                'user',
                $adminId,
                $notes
            );

            $appointment = $appointment->refresh();

            $this->auditLogService->log('appointments', 'status_change', $appointment, $before, $appointment->toArray());

            return $appointment;
        });
    }

    private function convertWaitingListRequest(
        Appointment $cancelledAppointment,
        int $waitingListRequestId,
        ?array $reschedule,
        ?int $adminId,
        ?string $notes
    ): void {
        $request = WaitingListRequest::query()->whereKey($waitingListRequestId)->first();

        if (! $request || $request->status->isFinalized()) {
            throw new InvalidArgumentException('Selected waiting-list request is not available.');
        }

        $durationMinutes = $this->resolveDurationMinutes($cancelledAppointment->start_time, $cancelledAppointment->end_time);
        $date = $reschedule['date'] ?? $cancelledAppointment->appointment_date?->toDateString() ?? now()->toDateString();
        $time = $reschedule['time'] ?? $cancelledAppointment->start_time ?? '09:00:00';
        $startAt = CarbonImmutable::parse("{$date} {$time}");

        $newAppointment = Appointment::query()->create([
            'patient_id' => $request->patient_id,
            'service_id' => $request->service_id,
            'assigned_doctor_id' => $cancelledAppointment->assigned_doctor_id,
            'appointment_date' => $startAt->toDateString(),
            'start_time' => $startAt->format('H:i:s'),
            'end_time' => $startAt->addMinutes($durationMinutes)->format('H:i:s'),
            'status' => AppointmentStatus::CONFIRMED,
            'booking_source' => $cancelledAppointment->booking_source,
            'notes' => $notes,
            'confirmed_at' => now(),
        ]);

        $request->update([
            'status' => WaitingListStatus::FULFILLED,
            'booked_appointment_id' => $newAppointment->id,
        ]);

        $this->statusLogService->createStatusLog(
            $newAppointment->id,
            null,
            AppointmentStatus::CONFIRMED->value,
            'user',
            $adminId,
            'Created from waiting-list conversion'
        );

        $this->auditLogService->log('appointments', 'create', $newAppointment, null, $newAppointment->toArray());
    }

    private function resolveDurationMinutes(?string $startTime, ?string $endTime): int
    {
        if (! $startTime || ! $endTime) {
            return 30;
        }

        $start = CarbonImmutable::parse($startTime);
        $end = CarbonImmutable::parse($endTime);
        $minutes = $start->diffInMinutes($end, false);

        return $minutes > 0 ? $minutes : 30;
    }
}

