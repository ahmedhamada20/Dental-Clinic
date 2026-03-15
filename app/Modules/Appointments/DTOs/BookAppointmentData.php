<?php

namespace App\Modules\Appointments\DTOs;

class BookAppointmentData
{
    public function __construct(
        public int $patient_id,
        public int $doctor_id,
        public int $specialty_id,
        public int $service_id,
        public string $appointment_date = '',
        public string $appointment_time = '',
        public ?string $notes = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            patient_id: $data['patient_id'],
            doctor_id: $data['doctor_id'] ?? $data['assigned_doctor_id'],
            specialty_id: $data['specialty_id'],
            service_id: $data['service_id'],
            appointment_date: $data['appointment_date'],
            appointment_time: $data['appointment_time'] ?? $data['start_time'],
            notes: $data['notes'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'patient_id' => $this->patient_id,
            'doctor_id' => $this->doctor_id,
            'specialty_id' => $this->specialty_id,
            'service_id' => $this->service_id,
            'appointment_date' => $this->appointment_date,
            'appointment_time' => $this->appointment_time,
            'notes' => $this->notes,
        ];
    }
}
