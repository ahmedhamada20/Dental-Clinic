<?php

namespace App\Modules\Medical\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MedicalRecordResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'patient_id' => $this->patient_id,
            'allergies' => $this->allergies,
            'medical_history' => $this->medical_history,
            'medications' => $this->medications,
            'created_at' => $this->created_at,
        ];
    }
}

