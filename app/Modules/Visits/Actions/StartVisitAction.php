<?php

namespace App\Modules\Visits\Actions;

use App\Models\Visit\Visit;
use App\Modules\Visits\DTOs\StartVisitData;
use App\Modules\Visits\Services\VisitService;

class StartVisitAction
{
    public function __construct(private readonly VisitService $service) {}

    public function execute(Visit $visit, StartVisitData $data): Visit
    {
        return $this->service->startVisit($visit, $data->clinicalNotes);
    }
}
