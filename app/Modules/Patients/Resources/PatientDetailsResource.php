<?php

namespace App\Modules\Patients\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PatientDetailsResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'patient_code' => $this->patient_code,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'alternate_phone' => $this->alternate_phone,
            'gender' => $this->gender,
            'date_of_birth' => $this->date_of_birth?->format('Y-m-d'),
            'age' => $this->age,
            'address' => $this->address,
            'city' => $this->city,
            'profile_image' => $this->profile_image,
            'status' => $this->status,
            'registered_from' => $this->registered_from,
            'last_login_at' => $this->last_login_at,
            'profile' => new PatientProfileResource($this->whenLoaded('profile')),
            'medical_history' => new PatientMedicalHistoryResource($this->whenLoaded('medicalHistory')),
            'emergency_contacts' => EmergencyContactResource::collection($this->whenLoaded('emergencyContacts')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

