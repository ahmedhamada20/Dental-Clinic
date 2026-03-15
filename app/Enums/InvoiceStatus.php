<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case UNPAID = 'unpaid';
    case PARTIALLY_PAID = 'partially_paid';
    case PAID = 'paid';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::UNPAID => 'Unpaid',
            self::PARTIALLY_PAID => 'Partially Paid',
            self::PAID => 'Paid',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function isPaid(): bool
    {
        return in_array($this, [self::PAID, self::PARTIALLY_PAID], true);
    }

    public function isActive(): bool
    {
        return in_array($this, [self::UNPAID, self::PARTIALLY_PAID], true);
    }
}

