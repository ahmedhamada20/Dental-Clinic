<?php

namespace App\Enums;

enum VisitStatus: string
{
    case CHECKED_IN = 'checked_in';
    case WITH_DOCTOR = 'with_doctor';
    case SCHEDULED = 'scheduled';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case NO_SHOW = 'no_show';

    public function label(): string
    {
        return match ($this) {
            self::CHECKED_IN => 'Checked In',
            self::WITH_DOCTOR => 'With Doctor',
            self::SCHEDULED => 'Scheduled',
            self::IN_PROGRESS => 'In Progress',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
            self::NO_SHOW => 'No Show',
        };
    }

    public function isActive(): bool
    {
        return in_array($this, [self::CHECKED_IN, self::WITH_DOCTOR, self::SCHEDULED, self::IN_PROGRESS], true);
    }
}

