<?php

namespace App\Modules\Medical\DTOs;

class CreatePrescriptionData
{
    public function __construct(
        public readonly ?string $notes = null
    ) {}
}
