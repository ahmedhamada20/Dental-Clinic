<?php

namespace App\Modules\Patients\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PatientProfileResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'patient_id' => $this->patient_id,
            'occupation' => $this->occupation,
            'marital_status' => $this->marital_status,
            'preferred_language' => $this->preferred_language,
            'blood_group' => $this->blood_group,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

