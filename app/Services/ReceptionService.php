<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Enums\TicketStatus;
use App\Enums\VisitStatus;
use App\Models\Appointment\Appointment;
use App\Models\Appointment\VisitTicket;
use App\Models\Visit\Visit;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * ReceptionService
 *
 * Handles patient check-in, queue management, and visit initiation.
 * This service manages the reception and workflow of patients from appointment to visit completion.
 */
class ReceptionService
{
    /**
     * Check in a patient from an appointment and create visit ticket.
     *
     * @param Appointment $appointment
     * @param int $checkedInBy User ID of the receptionist/staff member
     * @return array ['success' => bool, 'visit' => Visit, 'ticket' => VisitTicket, 'message' => string]
     */
    public function checkInPatient(Appointment $appointment, int $checkedInBy): array
    {
        return DB::transaction(function () use ($appointment, $checkedInBy) {
            // Validate appointment can be checked in
            if (!in_array($appointment->status->value, [AppointmentStatus::CONFIRMED->value, AppointmentStatus::PENDING->value])) {
                return [
                    'success' => false,
                    'message' => "Appointment status '{$appointment->status->label()}' cannot be checked in.",
                ];
            }

            // Check if visit already exists
            if ($appointment->visit) {
                return [
                    'success' => false,
                    'message' => "Visit already exists for this appointment.",
                ];
            }

            // Create visit
            $visit = Visit::create([
                'appointment_id' => $appointment->id,
                'patient_id' => $appointment->patient_id,
                'doctor_id' => $appointment->assigned_doctor_id,
                'checked_in_by' => $checkedInBy,
                'visit_date' => now()->toDateString(),
                'status' => VisitStatus::SCHEDULED,
            ]);

            // Create visit ticket
            $ticket = VisitTicket::create([
                'ticket_date' => now()->toDateString(),
                'ticket_number' => $this->generateTicketNumber(),
                'appointment_id' => $appointment->id,
                'visit_id' => $visit->id,
                'patient_id' => $appointment->patient_id,
                'status' => TicketStatus::ISSUED,
            ]);

            // Update appointment status
            $appointment->update([
                'status' => AppointmentStatus::CHECKED_IN,
                'checked_in_at' => now(),
            ]);

            return [
                'success' => true,
                'visit' => $visit->fresh()->load(['patient', 'doctor', 'appointment']),
                'ticket' => $ticket->fresh()->load(['patient', 'appointment']),
                'message' => 'Patient checked in successfully. Queue ticket #' . $ticket->ticket_number . ' issued.',
            ];
        });
    }

    /**
     * Call patient from queue and mark ticket as called.
     *
     * @param VisitTicket $ticket
     * @return array ['success' => bool, 'ticket' => VisitTicket, 'message' => string]
     */
    public function callPatientFromQueue(VisitTicket $ticket): array
    {
        return DB::transaction(function () use ($ticket) {
            if ($ticket->status->value !== TicketStatus::ISSUED->value) {
                return [
                    'success' => false,
                    'message' => "Ticket status '{$ticket->status->label()}' cannot be called.",
                ];
            }

            $ticket->update([
                'status' => TicketStatus::CALLED,
                'called_at' => now(),
            ]);

            // Update visit status to mark patient is being served
            if ($ticket->visit) {
                $ticket->visit->update(['status' => VisitStatus::IN_PROGRESS]);
            }

            return [
                'success' => true,
                'ticket' => $ticket->fresh()->load(['patient', 'visit']),
                'message' => 'Patient called from queue. Ready for service.',
            ];
        });
    }

    /**
     * Start a visit (call patient and begin service).
     *
     * @param Visit $visit
     * @return array ['success' => bool, 'visit' => Visit, 'message' => string]
     */
    public function startVisit(Visit $visit): array
    {
        return DB::transaction(function () use ($visit) {
            if (!in_array($visit->status->value, [VisitStatus::SCHEDULED->value, VisitStatus::IN_PROGRESS->value])) {
                return [
                    'success' => false,
                    'message' => "Visit status '{$visit->status->label()}' cannot be started.",
                ];
            }

            $visit->update([
                'status' => VisitStatus::IN_PROGRESS,
                'start_at' => now(),
            ]);

            // Update ticket status
            if ($visit->ticket) {
                $visit->ticket->update(['status' => TicketStatus::IN_SERVICE]);
            }

            return [
                'success' => true,
                'visit' => $visit->fresh()->load(['patient', 'doctor', 'ticket']),
                'message' => 'Visit started successfully.',
            ];
        });
    }

    /**
     * Mark patient as waiting.
     *
     * @param VisitTicket $ticket
     * @return array ['success' => bool, 'ticket' => VisitTicket, 'message' => string]
     */
    public function markPatientWaiting(VisitTicket $ticket): array
    {
        return DB::transaction(function () use ($ticket) {
            if (!in_array($ticket->status->value, [TicketStatus::CALLED->value, TicketStatus::IN_SERVICE->value])) {
                return [
                    'success' => false,
                    'message' => "Ticket status '{$ticket->status->label()}' is invalid for waiting.",
                ];
            }

            // Revert back to called status if not yet in service
            if ($ticket->status->value === TicketStatus::CALLED->value) {
                $ticket->update(['status' => TicketStatus::ISSUED]);
            }

            return [
                'success' => true,
                'ticket' => $ticket->fresh(),
                'message' => 'Patient marked as waiting.',
            ];
        });
    }

    /**
     * Complete a visit.
     *
     * @param Visit $visit
     * @param array $data Additional data (diagnosis, clinical_notes, etc.)
     * @return array ['success' => bool, 'visit' => Visit, 'message' => string]
     */
    public function completeVisit(Visit $visit, array $data = []): array
    {
        return DB::transaction(function () use ($visit, $data) {
            if ($visit->status->value === VisitStatus::COMPLETED->value) {
                return [
                    'success' => false,
                    'message' => 'Visit is already completed.',
                ];
            }

            if ($visit->status->value === VisitStatus::CANCELLED->value) {
                return [
                    'success' => false,
                    'message' => 'Cancelled visits cannot be completed.',
                ];
            }

            // Update visit with completion data
            $updateData = [
                'status' => VisitStatus::COMPLETED,
                'end_at' => now(),
            ];

            if (isset($data['chief_complaint'])) {
                $updateData['chief_complaint'] = $data['chief_complaint'];
            }
            if (isset($data['diagnosis'])) {
                $updateData['diagnosis'] = $data['diagnosis'];
            }
            if (isset($data['clinical_notes'])) {
                $updateData['clinical_notes'] = $data['clinical_notes'];
            }
            if (isset($data['internal_notes'])) {
                $updateData['internal_notes'] = $data['internal_notes'];
            }

            $visit->update($updateData);

            // Update appointment status
            if ($visit->appointment) {
                $visit->appointment->update(['status' => AppointmentStatus::COMPLETED]);
            }

            // Update ticket status
            if ($visit->ticket) {
                $visit->ticket->update([
                    'status' => TicketStatus::COMPLETED,
                    'finished_at' => now(),
                ]);
            }

            return [
                'success' => true,
                'visit' => $visit->fresh()->load(['patient', 'doctor', 'appointment', 'ticket']),
                'message' => 'Visit completed successfully.',
            ];
        });
    }

    /**
     * Cancel a visit.
     *
     * @param Visit $visit
     * @param string $reason Cancellation reason
     * @return array ['success' => bool, 'visit' => Visit, 'message' => string]
     */
    public function cancelVisit(Visit $visit, string $reason = 'Cancelled'): array
    {
        return DB::transaction(function () use ($visit, $reason) {
            if ($visit->status->value === VisitStatus::COMPLETED->value) {
                return [
                    'success' => false,
                    'message' => 'Completed visits cannot be cancelled.',
                ];
            }

            if ($visit->status->value === VisitStatus::CANCELLED->value) {
                return [
                    'success' => false,
                    'message' => 'Visit is already cancelled.',
                ];
            }

            $visit->update([
                'status' => VisitStatus::CANCELLED,
                'internal_notes' => ($visit->internal_notes ? $visit->internal_notes . "\n" : '') . "Cancelled: {$reason}",
            ]);

            // Update appointment status if needed
            if ($visit->appointment && $visit->appointment->status->value !== AppointmentStatus::COMPLETED->value) {
                $visit->appointment->update(['status' => AppointmentStatus::CANCELLED_BY_ADMIN]);
            }

            // Cancel ticket
            if ($visit->ticket) {
                $visit->ticket->update(['status' => TicketStatus::CANCELLED]);
            }

            return [
                'success' => true,
                'visit' => $visit->fresh()->load(['patient', 'doctor', 'appointment', 'ticket']),
                'message' => 'Visit cancelled successfully.',
            ];
        });
    }

    /**
     * Get today's queue.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTodaysQueue()
    {
        return VisitTicket::query()
            ->whereDate('ticket_date', now()->toDateString())
            ->whereIn('status', TicketStatus::databaseValuesFor([
                TicketStatus::ISSUED,
                TicketStatus::CALLED,
                TicketStatus::IN_SERVICE,
            ]))
            ->with(['patient', 'visit', 'appointment'])
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Get active visits (in progress).
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActiveVisits()
    {
        return Visit::query()
            ->where('status', VisitStatus::IN_PROGRESS->value)
            ->with(['patient', 'doctor', 'appointment', 'ticket'])
            ->orderBy('start_at', 'desc')
            ->get();
    }

    /**
     * Get completed visits for today.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCompletedVisitsToday()
    {
        return Visit::query()
            ->where('status', VisitStatus::COMPLETED->value)
            ->whereDate('visit_date', now()->toDateString())
            ->with(['patient', 'doctor', 'appointment', 'ticket'])
            ->orderBy('end_at', 'desc')
            ->get();
    }


    /**
     * Generate unique ticket number.
     *
     * @return string
     */
    private function generateTicketNumber(): string
    {
        $count = VisitTicket::whereDate('ticket_date', now()->toDateString())->count() + 1;

        return str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}

