<?php

namespace App\Enums;

enum PromotionType: string
{
    case INVOICE_PERCENT   = 'invoice_percent';
    case INVOICE_FIXED     = 'invoice_fixed';
    case SERVICE_PERCENT   = 'service_percent';
    case SERVICE_FIXED     = 'service_fixed';
    case FREE_CONSULTATION = 'free_consultation';

    public function label(): string
    {
        return match ($this) {
            self::INVOICE_PERCENT   => 'Invoice Percentage Discount',
            self::INVOICE_FIXED     => 'Invoice Fixed Discount',
            self::SERVICE_PERCENT   => 'Service Percentage Discount',
            self::SERVICE_FIXED     => 'Service Fixed Discount',
            self::FREE_CONSULTATION => 'Free Consultation',
        };
    }

    public static function options(): array
    {
        return array_map(fn($case) => [
            'value' => $case->value,
            'label' => $case->label(),
        ], self::cases());
    }
}
