<?php

namespace App\Enums;

enum TreatmentPlanStatus: string
{
    case DRAFT = 'draft';
    case PROPOSED = 'proposed';
    case APPROVED = 'approved';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::PROPOSED => 'Proposed',
            self::APPROVED => 'Approved',
            self::IN_PROGRESS => 'In Progress',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
            self::REJECTED => 'Rejected',
        };
    }

    public function isActive(): bool
    {
        return in_array($this, [self::PROPOSED, self::APPROVED, self::IN_PROGRESS]);
    }

    public function isEditable(): bool
    {
        return in_array($this, [self::DRAFT, self::PROPOSED]);
    }
}

