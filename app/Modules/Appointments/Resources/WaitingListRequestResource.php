<?php

namespace App\Modules\Appointments\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WaitingListRequestResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'patient' => [
                'id' => $this->patient->id,
                'patient_code' => $this->patient->patient_code,
                'full_name' => $this->patient->full_name,
                'phone' => $this->patient->phone,
            ],
            'service' => $this->service ? [
                'id' => $this->service->id,
                'name_en' => $this->service->name_en,
                'name_ar' => $this->service->name_ar,
            ] : null,
            'preferred_date' => $this->preferred_date?->format('Y-m-d'),
            'preferred_from_time' => $this->preferred_from_time,
            'preferred_to_time' => $this->preferred_to_time,
            'status' => $this->status?->value,
            'notified_at' => $this->notified_at?->format('Y-m-d H:i:s'),
            'expires_at' => $this->expires_at?->format('Y-m-d H:i:s'),
            'booked_appointment_id' => $this->booked_appointment_id,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}

