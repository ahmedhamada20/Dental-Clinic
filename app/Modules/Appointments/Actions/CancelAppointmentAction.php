<?php

namespace App\Modules\Appointments\Actions;

use App\Enums\AppointmentStatus;
use App\Models\Appointment\Appointment;
use App\Modules\Appointments\DTOs\CancelAppointmentData;
use App\Modules\Appointments\Services\AppointmentStatusService;

class CancelAppointmentAction
{
    public function __construct(
        private AppointmentStatusService $statusService,
    ) {}

    /**
     * Cancel an appointment.
     */
    public function __invoke(CancelAppointmentData $data): Appointment
    {
        $appointment = Appointment::findOrFail($data->appointment_id);
        $actorType = $data->cancelled_by_type === 'patient' ? 'patient' : 'user';

        // Check if appointment can be cancelled
        if (!$this->canCancel($appointment)) {
            throw new \Exception('Appointment cannot be cancelled in its current status');
        }

        $oldStatus = $appointment->status;

        // Determine new status based on who is cancelling
        $newStatus = $actorType === 'patient'
            ? AppointmentStatus::CANCELLED_BY_PATIENT
            : AppointmentStatus::CANCELLED_BY_ADMIN;

        // Update appointment
        $appointment->update([
            'status' => $newStatus,
            'cancellation_reason' => $data->cancellation_reason,
            'cancelled_at' => now(),
            'cancelled_by_type' => $actorType,
            'cancelled_by_id' => $data->cancelled_by_id,
        ]);

        // Create status log
        $this->statusService->createStatusLog(
            appointmentId: $appointment->id,
            oldStatus: $oldStatus->value,
            newStatus: $newStatus->value,
            changedByType: $actorType,
            changedById: $data->cancelled_by_id,
            notes: $data->cancellation_reason
        );

        return $appointment;
    }

    private function canCancel(Appointment $appointment): bool
    {
        // Can only cancel pending or confirmed appointments
        return in_array($appointment->status, [
            AppointmentStatus::PENDING,
            AppointmentStatus::CONFIRMED,
        ], true);
    }
}

