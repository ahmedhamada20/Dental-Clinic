<?php

namespace App\Modules\Patients\Actions;

use App\Models\Patient\Patient;
use App\Modules\Patients\DTOs\UpdatePatientDTO;

class UpdatePatientAction
{
    public function __invoke(Patient $patient, UpdatePatientDTO $dto): Patient
    {
        $data = $dto->toArray();

        // Remove null values
        $data = array_filter($data, fn($value) => $value !== null);

        if (!empty($data)) {
            $patient->update($data);
        }

        return $patient->refresh();
    }
}

