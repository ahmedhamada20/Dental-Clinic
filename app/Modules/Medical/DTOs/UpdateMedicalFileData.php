<?php

namespace App\Modules\Medical\DTOs;

class UpdateMedicalFileData
{
    public function __construct(
        public readonly ?string $fileCategory = null,
        public readonly ?string $title = null,
        public readonly ?string $notes = null,
        public readonly ?bool $isVisibleToPatient = null,
        public readonly ?int $visitId = null
    ) {}
}
