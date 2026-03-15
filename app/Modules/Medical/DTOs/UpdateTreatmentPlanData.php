<?php

namespace App\Modules\Medical\DTOs;

class UpdateTreatmentPlanData
{
    public function __construct(
        public readonly ?string $title = null,
        public readonly ?string $description = null,
        public readonly ?string $estimatedTotal = null,
        public readonly ?string $status = null,
        public readonly ?string $startDate = null,
        public readonly ?string $endDate = null
    ) {}
}
