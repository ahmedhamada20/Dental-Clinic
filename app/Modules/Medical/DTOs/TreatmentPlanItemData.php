<?php

namespace App\Modules\Medical\DTOs;

class TreatmentPlanItemData
{
    public function __construct(
        public readonly string $title,
        public readonly ?int $serviceId = null,
        public readonly ?int $toothNumber = null,
        public readonly ?string $description = null,
        public readonly ?int $sessionNo = null,
        public readonly ?string $estimatedCost = null,
        public readonly ?string $status = null,
        public readonly ?string $plannedDate = null
    ) {}
}
