<?php

namespace App\Modules\Patients\DTOs;

class UpdateMedicalHistoryDTO
{
    public function __construct(
        public ?string $allergies = null,
        public ?string $chronic_diseases = null,
        public ?string $current_medications = null,
        public ?string $medical_notes = null,
        public ?string $dental_history = null,
        public ?string $important_alerts = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            allergies: $data['allergies'] ?? null,
            chronic_diseases: $data['chronic_diseases'] ?? null,
            current_medications: $data['current_medications'] ?? null,
            medical_notes: $data['medical_notes'] ?? null,
            dental_history: $data['dental_history'] ?? null,
            important_alerts: $data['important_alerts'] ?? null,
        );
    }

    public function toArray(): array
    {
        $data = [];

        foreach ($this as $key => $value) {
            if ($value !== null) {
                $data[$key] = $value;
            }
        }

        return $data;
    }
}

