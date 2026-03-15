<?php

namespace App\Modules\Appointments\DTOs;

class CancelAppointmentData
{
    public function __construct(
        public int $appointment_id,
        public string $cancellation_reason,
        public string $cancelled_by_type = 'patient',
        public ?int $cancelled_by_id = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            appointment_id: $data['appointment_id'],
            cancellation_reason: $data['cancellation_reason'],
            cancelled_by_type: $data['cancelled_by_type'] ?? 'patient',
            cancelled_by_id: $data['cancelled_by_id'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'appointment_id' => $this->appointment_id,
            'cancellation_reason' => $this->cancellation_reason,
            'cancelled_by_type' => $this->cancelled_by_type,
            'cancelled_by_id' => $this->cancelled_by_id,
        ];
    }
}

