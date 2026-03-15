<?php

namespace App\Modules\Patients\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MedicalSummaryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'patient_id' => $this->id,
            'patient_code' => $this->patient_code,
            'full_name' => $this->full_name,
            'age' => $this->age,
            'gender' => $this->gender,
            'contact' => [
                'phone' => $this->phone,
                'email' => $this->email,
                'address' => $this->address,
                'city' => $this->city,
            ],
            'profile' => new PatientProfileResource($this->whenLoaded('profile')),
            'medical_history' => new PatientMedicalHistoryResource($this->whenLoaded('medicalHistory')),
            'emergency_contacts' => EmergencyContactResource::collection($this->whenLoaded('emergencyContacts')),
            'last_visit' => $this->whenLoaded('visits', function () {
                return $this->visits()->latest()->first();
            }),
        ];
    }
}

