<?php

namespace App\Modules\Visits\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VisitResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'visit_no' => $this->visit_no,
            'appointment_id' => $this->appointment_id,
            'patient_id' => $this->patient_id,
            'doctor_id' => $this->doctor_id,
            'visit_date' => $this->visit_date,
            'start_at' => $this->start_at,
            'end_at' => $this->end_at,
            'status' => $this->status?->value ?? $this->status,
            'chief_complaint' => $this->chief_complaint,
            'diagnosis' => $this->diagnosis,
            'clinical_notes' => $this->clinical_notes,
            'internal_notes' => $this->internal_notes,
            'patient' => $this->whenLoaded('patient', fn () => [
                'id' => $this->patient?->id,
                'full_name' => $this->patient?->full_name,
                'patient_code' => $this->patient?->patient_code,
            ]),
            'doctor' => $this->whenLoaded('doctor', fn () => [
                'id' => $this->doctor?->id,
                'name' => $this->doctor?->name,
            ]),
        ];
    }
}
