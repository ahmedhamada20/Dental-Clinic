<?php

namespace App\Modules\Dashboard\Services;

use App\Models\Appointment\Appointment;

class TodayQueueService
{
    public function getQueue(): array
    {
        $today = now()->toDateString();

        return Appointment::query()
            ->with(['patient.user:id,name', 'doctor:id,name'])
            ->whereDate('appointment_date', $today)
            ->orderBy('appointment_time')
            ->get()
            ->map(function ($appointment): array {
                return [
                    'appointment_id' => $appointment->id,
                    'patient_id' => $appointment->patient_id,
                    'patient_name' => optional(optional($appointment->patient)->user)->name,
                    'doctor_id' => $appointment->doctor_id,
                    'doctor_name' => optional($appointment->doctor)->name,
                    'appointment_time' => $appointment->appointment_time,
                    'status' => $appointment->status,
                ];
            })
            ->toArray();
    }
}
