<?php

namespace App\Modules\Appointments\Services;

use App\Models\Appointment\WaitingListRequest;
use App\Enums\WaitingListStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class WaitingListService
{
    /**
     * The default expiration time for waiting list requests (7 days).
     */
    private int $expirationDays = 7;

    /**
     * Get active waiting list requests for a patient.
     */
    public function getActiveWaitingListRequests(int $patientId): Collection
    {
        return WaitingListRequest::where('patient_id', $patientId)
            ->whereIn('status', [WaitingListStatus::PENDING, WaitingListStatus::NOTIFIED])
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->get();
    }

    /**
     * Check if a waiting list request is valid for claiming.
     */
    public function isRequestValidForClaim(WaitingListRequest $request): bool
    {
        // Check if status is active
        if (!in_array($request->status, [WaitingListStatus::PENDING, WaitingListStatus::NOTIFIED], true)) {
            return false;
        }

        // Check if not expired
        if ($request->expires_at && $request->expires_at->isPast()) {
            return false;
        }

        // Check if not already booked
        if ($request->booked_appointment_id) {
            return false;
        }

        return true;
    }

    /**
     * Calculate expiration date for waiting list request.
     */
    public function calculateExpirationDate(): Carbon
    {
        return now()->addDays($this->expirationDays);
    }

    /**
     * Set expiration days for waiting list requests.
     */
    public function setExpirationDays(int $days): self
    {
        $this->expirationDays = $days;
        return $this;
    }
}

