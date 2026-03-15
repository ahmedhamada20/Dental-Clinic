<?php

namespace App\Enums;

enum DiscountType: string
{
    case PERCENT = 'percent';
    case FIXED = 'fixed';
    case PROMOTION = 'promotion';

    public function label(): string
    {
        return match ($this) {
            self::PERCENT => 'Percent',
            self::FIXED => 'Fixed',
            self::PROMOTION => 'Promotion',
        };
    }
}

