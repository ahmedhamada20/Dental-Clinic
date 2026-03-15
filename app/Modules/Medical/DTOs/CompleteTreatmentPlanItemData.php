<?php

namespace App\Modules\Medical\DTOs;

class CompleteTreatmentPlanItemData
{
    public function __construct(
        public readonly ?int $completedVisitId = null
    ) {}
}
