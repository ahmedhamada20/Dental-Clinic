<?php

namespace App\Modules\Reports\Services;

use App\Models\Billing\Promotion;
use App\Modules\Reports\DTOs\ReportFilterDTO;

class PromotionReportService
{
    public function generate(ReportFilterDTO $dto): array
    {
        $query = Promotion::query();

        return [
            'filters' => (array) $dto,

            'rows' => (clone $query)->latest('id')->get()->toArray(),
        ];
    }
}
