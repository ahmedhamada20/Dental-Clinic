<?php

namespace App\Modules\Billing\Services;

use App\Models\Billing\Promotion;
use Carbon\Carbon;

/**
 * Service for resolving and validating promotions
 */
class PromotionResolverService
{
    /**
     * Find promotion by code
     */
    public function findByCode(string $code): ?Promotion
    {
        return Promotion::where('code', strtoupper($code))->first();
    }

    /**
     * Check if promotion is valid and active
     */
    public function isValid(Promotion $promotion): bool
    {
        if (!$promotion->is_active) {
            return false;
        }

        $now = Carbon::now();
        return $promotion->starts_at <= $now && $promotion->ends_at >= $now;
    }

    /**
     * Get active promotions
     */
    public function getActivePromotions(): \Illuminate\Database\Eloquent\Collection
    {
        return Promotion::activeNow()->get();
    }

    /**
     * Validate promotion is applicable for a given amount
     */
    public function isApplicable(Promotion $promotion, float $amount): bool
    {
        if (!$this->isValid($promotion)) {
            return false;
        }

        // If fixed discount type, ensure amount is greater than discount value
        if ($promotion->promotion_type->value === 'fixed') {
            return $amount > $promotion->value;
        }

        return true;
    }

    /**
     * Calculate discount amount from promotion
     */
    public function calculateDiscount(Promotion $promotion, float $amount): float
    {
        if (!$this->isApplicable($promotion, $amount)) {
            return 0;
        }

        return match($promotion->promotion_type->value) {
            'percentage' => ($amount * $promotion->value) / 100,
            'fixed' => min($promotion->value, $amount),
            default => 0,
        };
    }

    /**
     * Get promotion details with validation
     */
    public function getPromotionDetails(Promotion $promotion): array
    {
        return [
            'id' => $promotion->id,
            'code' => $promotion->code,
            'title_ar' => $promotion->title_ar,
            'title_en' => $promotion->title_en,
            'type' => $promotion->promotion_type->value,
            'value' => (float)$promotion->value,
            'is_valid' => $this->isValid($promotion),
            'is_active' => $promotion->is_active,
            'starts_at' => $promotion->starts_at?->format('Y-m-d'),
            'ends_at' => $promotion->ends_at?->format('Y-m-d'),
        ];
    }
}

