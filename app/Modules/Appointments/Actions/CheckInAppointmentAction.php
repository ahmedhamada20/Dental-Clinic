<?php

namespace App\Modules\Appointments\Actions;

use App\Enums\AppointmentStatus;
use App\Enums\VisitStatus;
use App\Enums\TicketStatus;
use App\Models\Appointment\Appointment;
use App\Models\Appointment\VisitTicket;
use App\Models\Visit\Visit;
use App\Modules\Appointments\Services\AppointmentStatusService;
use App\Modules\Appointments\Services\TicketNumberGeneratorService;

class CheckInAppointmentAction
{
    public function __construct(
        private AppointmentStatusService $statusService,
        private TicketNumberGeneratorService $ticketGenerator,
    ) {}

    /**
     * Check in an appointment - creates visit, ticket, and status log.
     */
    public function __invoke(int $appointmentId, int $checkedInBy, ?string $notes = null): array
    {
        $appointment = Appointment::findOrFail($appointmentId);

        // Check if appointment can be checked in
        if ($appointment->status !== AppointmentStatus::CONFIRMED) {
            throw new \Exception('Only confirmed appointments can be checked in');
        }

        $oldStatus = $appointment->status;

        // Begin transaction
        \DB::beginTransaction();

        try {
            // Update appointment status
            $appointment->update([
                'status' => AppointmentStatus::CHECKED_IN,
                'checked_in_at' => now(),
            ]);

            // Create visit
            $visit = Visit::create([
                'appointment_id' => $appointment->id,
                'patient_id' => $appointment->patient_id,
                'doctor_id' => $appointment->assigned_doctor_id ?? auth()->id(),
                'checked_in_by' => $checkedInBy,
                'visit_date' => now()->toDateString(),
                'status' => VisitStatus::IN_PROGRESS,
            ]);

            // Create ticket
            $ticket = VisitTicket::create([
                'ticket_number' => $this->ticketGenerator->generateTicketNumber(),
                'ticket_date' => now()->toDateString(),
                'appointment_id' => $appointment->id,
                'visit_id' => $visit->id,
                'patient_id' => $appointment->patient_id,
                'status' => TicketStatus::ISSUED,
            ]);

            // Create status log
            $this->statusService->createStatusLog(
                appointmentId: $appointment->id,
                oldStatus: $oldStatus->value,
                newStatus: AppointmentStatus::CHECKED_IN->value,
                changedByType: 'user',
                changedById: $checkedInBy,
                notes: $notes ?? 'Patient checked in'
            );

            \DB::commit();

            return [
                'appointment' => $appointment->fresh(),
                'visit' => $visit,
                'ticket' => $ticket,
            ];
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }

}

