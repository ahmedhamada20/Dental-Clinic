<?php

namespace App\Modules\Medical\DTOs;

class CreateTreatmentPlanData
{
    public function __construct(
        public readonly string $title,
        public readonly ?string $description,
        public readonly ?string $estimatedTotal,
        public readonly ?string $status,
        public readonly ?string $startDate,
        public readonly ?string $endDate,
        public readonly ?int $visitId
    ) {}
}
