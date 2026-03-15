<?php

namespace App\Modules\Visits\Actions;

use App\Models\Visit\Visit;
use App\Modules\Visits\DTOs\CompleteVisitData;
use App\Modules\Visits\Services\VisitService;

class CompleteVisitAction
{
    public function __construct(private readonly VisitService $service) {}

    public function execute(Visit $visit, CompleteVisitData $data): Visit
    {
        return $this->service->completeVisit(
            $visit,
            $data->diagnosis,
            $data->clinicalNotes,
            $data->internalNotes
        );
    }
}
