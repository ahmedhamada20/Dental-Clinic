<?php

namespace App\Modules\Appointments\Services;

use App\Models\Appointment\AppointmentStatusLog;
use Illuminate\Database\Eloquent\Collection;

class AppointmentStatusService
{
    /**
     * Create a status log for appointment status change.
     */
    public function createStatusLog(
        int $appointmentId,
        ?string $oldStatus,
        string $newStatus,
        ?string $changedByType = null,
        ?int $changedById = null,
        ?string $notes = null
    ): AppointmentStatusLog
    {
        return AppointmentStatusLog::create([
            'appointment_id' => $appointmentId,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'changed_by_type' => $changedByType,
            'changed_by_id' => $changedById,
            'notes' => $notes,
            'created_at' => now(),
        ]);
    }

    /**
     * Get all status logs for an appointment.
     */
    public function getStatusLogs(int $appointmentId): Collection
    {
        return AppointmentStatusLog::where('appointment_id', $appointmentId)
            ->orderByDesc('created_at')
            ->get();
    }
}

