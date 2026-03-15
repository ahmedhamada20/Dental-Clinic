<?php

namespace App\Enums;

enum FileCategory: string
{
    case PRESCRIPTION = 'prescription';
    case XRAY = 'xray';
    case TREATMENT_DOCUMENT = 'treatment_document';
    case BEFORE_AFTER = 'before_after';
    case LAB_RESULT = 'lab_result';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::PRESCRIPTION => 'Prescription',
            self::XRAY => 'X-Ray',
            self::TREATMENT_DOCUMENT => 'Treatment Document',
            self::BEFORE_AFTER => 'Before / After',
            self::LAB_RESULT => 'Lab Result',
            self::OTHER => 'Other',
        };
    }
}

