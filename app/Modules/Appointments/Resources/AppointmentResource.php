<?php

namespace App\Modules\Appointments\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'appointment_no' => $this->appointment_no,
            'patient_id' => $this->patient_id,
            'patient_name' => $this->patient?->full_name,
            'service_id' => $this->service_id,
            'service_name' => $this->service?->name_en,
            'assigned_doctor_id' => $this->assigned_doctor_id,
            'doctor_name' => $this->doctor?->full_name,
            'appointment_date' => $this->appointment_date?->format('Y-m-d'),
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'status' => $this->status?->value,
            'booking_source' => $this->booking_source?->value,
            'notes' => $this->notes,
            'confirmed_at' => $this->confirmed_at?->format('Y-m-d H:i:s'),
            'checked_in_at' => $this->checked_in_at?->format('Y-m-d H:i:s'),
            'cancellation_reason' => $this->cancellation_reason,
            'cancelled_at' => $this->cancelled_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}

