<?php

namespace App\Enums;

enum PatientStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case SUSPENDED = 'suspended';
    case ARCHIVED = 'archived';
    case BLOCKED = 'blocked';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::INACTIVE => 'Inactive',
            self::SUSPENDED => 'Suspended',
            self::BLOCKED => 'blocked',
            self::ARCHIVED => 'Archived',
        };
    }

    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }
}

