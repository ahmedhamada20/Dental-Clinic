<?php

namespace App\Modules\Medical\DTOs;

class PrescriptionItemData
{
    public function __construct(
        public readonly string $medicineName,
        public readonly ?string $dosage = null,
        public readonly ?string $frequency = null,
        public readonly ?string $duration = null,
        public readonly ?string $instructions = null
    ) {}
}
