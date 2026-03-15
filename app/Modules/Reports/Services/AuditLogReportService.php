<?php

namespace App\Modules\Reports\Services;

use App\Models\System\AuditLog;
use App\Modules\Reports\DTOs\ReportFilterDTO;

class AuditLogReportService
{
    public function generate(ReportFilterDTO $dto): array
    {
        $query = AuditLog::query()
            ->when($dto->fromDate, fn ($q) => $q->whereDate('created_at', '>=', $dto->fromDate))
            ->when($dto->toDate, fn ($q) => $q->whereDate('created_at', '<=', $dto->toDate));

        return [
            'filters' => (array) $dto,
            'summary' => [
                'total_logs' => (clone $query)->count(),
            ],
            'rows' => (clone $query)->latest('created_at')->limit(500)->get()->toArray(),
        ];
    }
}
