<?php

namespace App\Modules\Billing\Services;

use App\Enums\DiscountType;
use App\Models\Billing\Invoice;
use App\Models\Billing\Promotion;
use Illuminate\Support\Facades\DB;

class InvoiceCalculationService
{
    /**
     * Calculate invoice totals from items
     */
    public function calculateTotals(Invoice $invoice): array
    {
        $subtotal = (float) $invoice->items()->sum(DB::raw('(unit_price * quantity) - COALESCE(discount_amount, 0)'));

        $promotionDiscount = 0.0;
        if ($invoice->promotion_id) {
            $promotion = Promotion::find($invoice->promotion_id);
            if ($promotion && $promotion->is_active && $promotion->starts_at <= now() && $promotion->ends_at >= now()) {
                $promotionDiscount = $this->calculateDiscountAmount(
                    amount: $subtotal,
                    promotionType: $promotion->promotion_type->value,
                    discountValue: (float) $promotion->value
                );
            }
        }

        $manualDiscount = $this->resolveManualDiscount($invoice, $subtotal - $promotionDiscount);
        $discountAmount = min($subtotal, $promotionDiscount + $manualDiscount);
        $totalAmount = max(0, $subtotal - $discountAmount);

        return [
            'subtotal' => round($subtotal, 2),
            'promotion_discount' => round($promotionDiscount, 2),
            'manual_discount' => round($manualDiscount, 2),
            'discount_amount' => round($discountAmount, 2),
            'total' => round($totalAmount, 2),
        ];
    }

    /**
     * Calculate discount amount based on type and value
     */
    public function calculateDiscountAmount(float $amount, string $promotionType, float $discountValue): float
    {
        return match ($promotionType) {
            'percentage' => ($amount * $discountValue) / 100,
            'fixed' => min($discountValue, $amount),
            default => 0,
        };
    }

    /**
     * Calculate remaining amount to be paid
     */
    public function calculateRemainingAmount(Invoice $invoice): float
    {
        return max(0, round((float) $invoice->total - (float) $invoice->paid_amount, 2));
    }

    /**
     * Validate if total items match invoice total
     */
    public function validateItemsTotals(Invoice $invoice): bool
    {
        $calculatedTotals = $this->calculateTotals($invoice);
        $tolerance = 0.01;

        return abs($calculatedTotals['total'] - (float) $invoice->total) <= $tolerance;
    }

    /**
     * Resolve manual discount based on type and value
     */
    private function resolveManualDiscount(Invoice $invoice, float $discountableAmount): float
    {
        if (! $invoice->discount_type || $invoice->discount_value === null) {
            return 0.0;
        }

        return match ($invoice->discount_type) {
            DiscountType::PERCENT => min($discountableAmount, ($discountableAmount * (float) $invoice->discount_value) / 100),
            DiscountType::FIXED => min($discountableAmount, (float) $invoice->discount_value),
            DiscountType::PROMOTION => 0.0,
            default => 0.0,
        };
    }
}
