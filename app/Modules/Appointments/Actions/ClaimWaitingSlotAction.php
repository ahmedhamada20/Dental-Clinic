<?php

namespace App\Modules\Appointments\Actions;

use App\Enums\WaitingListStatus;
use App\Models\Appointment\WaitingListRequest;
use App\Modules\Appointments\Services\WaitingListService;

class ClaimWaitingSlotAction
{
    public function __construct(
        private WaitingListService $waitingListService,
    ) {}

    /**
     * Claim a slot from waiting list and create appointment.
     */
    public function __invoke(int $waitingListRequestId, string $appointmentDate, string $startTime): WaitingListRequest
    {
        $request = WaitingListRequest::findOrFail($waitingListRequestId);

        // Verify request is valid for claiming
        if (!$this->waitingListService->isRequestValidForClaim($request)) {
            throw new \Exception('This waiting list request cannot be claimed');
        }

        // Verify patient owns this request
        if ($request->patient_id !== auth()->id()) {
            throw new \Exception('Unauthorized to claim this waiting list request');
        }

        // Create appointment through BookAppointmentAction
        $bookAction = app(BookAppointmentAction::class);
        $appointmentData = new \App\Modules\Appointments\DTOs\BookAppointmentData(
            patient_id: $request->patient_id,
            service_id: $request->service_id,
            appointment_date: $appointmentDate,
            start_time: $startTime,
        );

        $appointment = $bookAction($appointmentData);

        // Update waiting list request
        $request->update([
            'status' => WaitingListStatus::CLAIMED,
            'booked_appointment_id' => $appointment->id,
            'notified_at' => now(),
        ]);

        return $request;
    }
}

