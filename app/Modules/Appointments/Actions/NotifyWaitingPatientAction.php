<?php

namespace App\Modules\Appointments\Actions;

use App\Models\Appointment\WaitingListRequest;

class NotifyWaitingPatientAction
{
    /**
     * Notify a waiting patient about available slot.
     * Can be extended to send push notifications, emails, etc.
     */
    public function __invoke(WaitingListRequest $waitingListRequest): void
    {
        // Mark as notified
        $waitingListRequest->update([
            'notified_at' => now(),
        ]);

        // TODO: Send notification to patient
        // - Send push notification
        // - Send email
        // - Send SMS
    }
}

