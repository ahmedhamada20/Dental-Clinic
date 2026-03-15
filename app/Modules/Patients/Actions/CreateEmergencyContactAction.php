<?php

namespace App\Modules\Patients\Actions;

use App\Models\Patient\EmergencyContact;
use App\Models\Patient\Patient;
use App\Modules\Patients\DTOs\EmergencyContactDTO;

class CreateEmergencyContactAction
{
    public function __invoke(Patient $patient, EmergencyContactDTO $dto): EmergencyContact
    {
        $data = $dto->toArray();
        $data['patient_id'] = $patient->id;

        return EmergencyContact::create($data);
    }
}

