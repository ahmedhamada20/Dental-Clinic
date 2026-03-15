<?php

namespace App\Modules\Visits\DTOs;

class VisitNoteData
{
    public function __construct(
        public readonly string $noteType,
        public readonly string $note
    ) {}
}
