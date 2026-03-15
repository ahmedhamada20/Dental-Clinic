<?php

namespace App\Modules\Visits\DTOs;

class VisitDTO
{
    public function __construct(
        public int $appointment_id,
        public ?string $notes = null,
        public ?string $diagnosis = null,
    ) {}
}

