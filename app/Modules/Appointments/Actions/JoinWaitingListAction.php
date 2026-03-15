<?php

namespace App\Modules\Appointments\Actions;

use App\Enums\WaitingListStatus;
use App\Models\Appointment\WaitingListRequest;
use App\Modules\Appointments\DTOs\WaitingListRequestData;
use App\Modules\Appointments\Services\WaitingListService;

class JoinWaitingListAction
{
    public function __construct(
        private WaitingListService $waitingListService,
    ) {}

    /**
     * Add patient to waiting list.
     */
    public function __invoke(WaitingListRequestData $data): WaitingListRequest
    {
        // Create waiting list request
        $request = WaitingListRequest::create([
            'patient_id' => $data->patient_id,
            'service_id' => $data->service_id,
            'preferred_date' => $data->preferred_date,
            'preferred_from_time' => $data->preferred_from_time,
            'preferred_to_time' => $data->preferred_to_time,
            'status' => WaitingListStatus::PENDING,
            'expires_at' => $this->waitingListService->calculateExpirationDate(),
        ]);

        return $request;
    }
}

