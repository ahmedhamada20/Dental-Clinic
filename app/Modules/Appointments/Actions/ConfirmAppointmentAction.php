<?php

namespace App\Modules\Appointments\Actions;

use App\Enums\AppointmentStatus;
use App\Models\Appointment\Appointment;
use App\Modules\Appointments\Services\AppointmentStatusService;

class ConfirmAppointmentAction
{
    public function __construct(
        private AppointmentStatusService $statusService,
    ) {}

    /**
     * Confirm a pending appointment.
     */
    public function __invoke(int $appointmentId, ?int $confirmedById = null, ?string $notes = null): Appointment
    {
        $appointment = Appointment::findOrFail($appointmentId);

        // Check if appointment can be confirmed
        if ($appointment->status !== AppointmentStatus::PENDING) {
            throw new \Exception('Only pending appointments can be confirmed');
        }

        $oldStatus = $appointment->status;

        // Update appointment
        $appointment->update([
            'status' => AppointmentStatus::CONFIRMED,
            'confirmed_at' => now(),
        ]);

        // Create status log
        $this->statusService->createStatusLog(
            appointmentId: $appointment->id,
            oldStatus: $oldStatus->value,
            newStatus: AppointmentStatus::CONFIRMED->value,
            changedByType: 'user',
            changedById: $confirmedById,
            notes: $notes ?? 'Appointment confirmed by admin'
        );

        return $appointment;
    }
}

