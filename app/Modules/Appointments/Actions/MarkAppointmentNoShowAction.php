<?php

namespace App\Modules\Appointments\Actions;

use App\Enums\AppointmentStatus;
use App\Models\Appointment\Appointment;
use App\Modules\Appointments\Services\AppointmentStatusService;

class MarkAppointmentNoShowAction
{
    public function __construct(
        private AppointmentStatusService $statusService,
    ) {}

    /**
     * Mark an appointment as no-show.
     */
    public function __invoke(int $appointmentId, int $markedBy, ?string $notes = null): Appointment
    {
        $appointment = Appointment::findOrFail($appointmentId);

        // Check if appointment can be marked as no-show
        if ($appointment->status !== AppointmentStatus::CONFIRMED) {
            throw new \Exception('Only confirmed appointments can be marked as no-show');
        }

        $oldStatus = $appointment->status;

        // Update appointment
        $appointment->update([
            'status' => AppointmentStatus::NO_SHOW,
        ]);

        // Create status log
        $this->statusService->createStatusLog(
            appointmentId: $appointment->id,
            oldStatus: $oldStatus->value,
            newStatus: AppointmentStatus::NO_SHOW->value,
            changedByType: 'user',
            changedById: $markedBy,
            notes: $notes ?? 'Patient did not show up'
        );

        return $appointment;
    }
}

