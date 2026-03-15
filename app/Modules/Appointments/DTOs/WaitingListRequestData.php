<?php

namespace App\Modules\Appointments\DTOs;

use DateTime;

class WaitingListRequestData
{
    public function __construct(
        public int $patient_id,
        public ?int $service_id = null,
        public ?string $preferred_date = null,
        public ?string $preferred_from_time = null,
        public ?string $preferred_to_time = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            patient_id: $data['patient_id'],
            service_id: $data['service_id'] ?? null,
            preferred_date: $data['preferred_date'] ?? null,
            preferred_from_time: $data['preferred_from_time'] ?? null,
            preferred_to_time: $data['preferred_to_time'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'patient_id' => $this->patient_id,
            'service_id' => $this->service_id,
            'preferred_date' => $this->preferred_date,
            'preferred_from_time' => $this->preferred_from_time,
            'preferred_to_time' => $this->preferred_to_time,
        ];
    }
}

