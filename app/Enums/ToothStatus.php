<?php

namespace App\Enums;

enum ToothStatus: string
{
    case HEALTHY = 'healthy';
    case CAVITIES = 'cavities';
    case ROOT_CANAL = 'root_canal';
    case CROWN = 'crown';
    case BRIDGE = 'bridge';
    case IMPLANT = 'implant';
    case EXTRACTION = 'extraction';
    case MISSING = 'missing';
    case TREATMENT_NEEDED = 'treatment_needed';

    public function label(): string
    {
        return match ($this) {
            self::HEALTHY => 'Healthy',
            self::CAVITIES => 'Cavities',
            self::ROOT_CANAL => 'Root Canal',
            self::CROWN => 'Crown',
            self::BRIDGE => 'Bridge',
            self::IMPLANT => 'Implant',
            self::EXTRACTION => 'Extraction',
            self::MISSING => 'Missing',
            self::TREATMENT_NEEDED => 'Treatment Needed',
        };
    }

    public function isHealthy(): bool
    {
        return $this === self::HEALTHY;
    }

    public function needsTreatment(): bool
    {
        return in_array($this, [self::CAVITIES, self::TREATMENT_NEEDED]);
    }
}

