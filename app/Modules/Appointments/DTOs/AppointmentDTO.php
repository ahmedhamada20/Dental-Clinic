<?php

namespace App\Modules\Appointments\DTOs;

class AppointmentDTO
{
    public function __construct(
        public int $patient_id,
        public int $dentist_id,
        public string $start_time,
        public string $end_time,
        public ?string $notes = null,
    ) {}
}

