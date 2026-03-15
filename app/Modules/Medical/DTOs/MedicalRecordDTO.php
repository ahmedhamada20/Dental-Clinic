<?php

namespace App\Modules\Medical\DTOs;

class MedicalRecordDTO
{
    public function __construct(
        public int $patient_id,
        public ?string $allergies = null,
        public ?string $medical_history = null,
        public ?string $medications = null,
    ) {}
}

