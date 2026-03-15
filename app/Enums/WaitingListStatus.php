<?php

namespace App\Enums;

enum WaitingListStatus: string
{
    case PENDING = 'waiting';
    case NOTIFIED = 'notified';
    case FULFILLED = 'booked';
    case EXPIRED = 'expired';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'waiting',
            self::NOTIFIED => 'notified',
            self::FULFILLED => 'booked',
            self::EXPIRED => 'expired',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::PENDING => 'bg-warning text-dark',
            self::NOTIFIED => 'bg-info text-white',
            self::FULFILLED => 'bg-success text-white',
            self::EXPIRED => 'bg-dark text-white',
            self::CANCELLED => 'bg-secondary text-white',
        };
    }

    public function isActive(): bool
    {
        return in_array($this, [self::PENDING, self::NOTIFIED], true);
    }

    public function isFinalized(): bool
    {
        return in_array($this, [self::FULFILLED, self::CANCELLED, self::EXPIRED], true);
    }
}

