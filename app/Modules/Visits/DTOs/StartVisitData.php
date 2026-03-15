<?php

namespace App\Modules\Visits\DTOs;

class StartVisitData
{
    public function __construct(
        public readonly ?string $clinicalNotes = null
    ) {}
}
