<?php

namespace App\Enums;

enum AppointmentStatus: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case CHECKED_IN = 'checked_in';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case CANCELLED_BY_PATIENT = 'cancelled_by_patient';
    // Keep case name for backward compatibility; DB stores cancelled_by_clinic.
    case CANCELLED_BY_ADMIN = 'cancelled_by_clinic';
    case NO_SHOW = 'no_show';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::CONFIRMED => 'Confirmed',
            self::CHECKED_IN => 'Checked In',
            self::IN_PROGRESS => 'In Progress',
            self::COMPLETED => 'Completed',
            self::CANCELLED_BY_PATIENT => 'Cancelled by Patient',
            self::CANCELLED_BY_ADMIN => 'Cancelled by Clinic',
            self::NO_SHOW => 'No Show',
        };
    }

    public function isActive(): bool
    {
        return in_array($this, [self::PENDING, self::CONFIRMED, self::CHECKED_IN, self::IN_PROGRESS], true);
    }

    public function isCompleted(): bool
    {
        return in_array($this, [self::COMPLETED, self::CANCELLED_BY_PATIENT, self::CANCELLED_BY_ADMIN, self::NO_SHOW], true);
    }
}

