<?php

namespace App\Modules\Medical\DTOs;

use Illuminate\Http\UploadedFile;

class UploadMedicalFileData
{
    public function __construct(
        public readonly UploadedFile $file,
        public readonly string $fileCategory,
        public readonly string $title,
        public readonly ?string $notes = null,
        public readonly ?int $visitId = null,
        public readonly bool $isVisibleToPatient = true
    ) {}
}
