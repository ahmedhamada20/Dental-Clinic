<?php

namespace App\Modules\Medical\DTOs;

class UpdateToothStatusData
{
    public function __construct(
        public readonly int $toothNumber,
        public readonly string $status,
        public readonly ?string $surface = null,
        public readonly ?string $notes = null,
        public readonly ?int $visitId = null
    ) {}
}
