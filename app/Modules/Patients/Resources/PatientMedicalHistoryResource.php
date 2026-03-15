<?php

namespace App\Modules\Patients\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PatientMedicalHistoryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'patient_id' => $this->patient_id,
            'allergies' => $this->allergies,
            'chronic_diseases' => $this->chronic_diseases,
            'current_medications' => $this->current_medications,
            'medical_notes' => $this->medical_notes,
            'dental_history' => $this->dental_history,
            'important_alerts' => $this->important_alerts,
            'updated_by' => $this->updated_by,
            'updated_by_user' => $this->whenLoaded('updatedBy'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

