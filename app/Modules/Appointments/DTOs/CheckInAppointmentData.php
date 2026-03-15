<?php

namespace App\Modules\Appointments\DTOs;

class CheckInAppointmentData
{
    public function __construct(
        public int $appointment_id,
        public int $checked_in_by,
        public ?string $notes = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            appointment_id: $data['appointment_id'],
            checked_in_by: $data['checked_in_by'],
            notes: $data['notes'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'appointment_id' => $this->appointment_id,
            'checked_in_by' => $this->checked_in_by,
            'notes' => $this->notes,
        ];
    }
}

