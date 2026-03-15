<?php

namespace App\Modules\Patients\Actions;

use App\Models\Patient\Patient;
use App\Modules\Patients\DTOs\UpdateMedicalHistoryDTO;

class UpdatePatientMedicalHistoryAction
{
    public function __invoke(Patient $patient, UpdateMedicalHistoryDTO $dto): void
    {
        $data = $dto->toArray();
        $data['updated_by'] = auth('sanctum')->user()?->id;

        $medicalHistory = $patient->medicalHistory;

        if ($medicalHistory) {
            $medicalHistory->update($data);
        } else {
            $data['patient_id'] = $patient->id;
            $patient->medicalHistory()->create($data);
        }
    }
}

