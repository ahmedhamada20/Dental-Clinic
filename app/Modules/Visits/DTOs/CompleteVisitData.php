<?php

namespace App\Modules\Visits\DTOs;

class CompleteVisitData
{
    public function __construct(
        public readonly ?string $diagnosis = null,
        public readonly ?string $clinicalNotes = null,
        public readonly ?string $internalNotes = null
    ) {}
}
