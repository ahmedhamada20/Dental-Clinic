<?php

namespace App\Modules\Billing\Actions;

use App\Models\Billing\Promotion;
use App\Modules\Billing\DTOs\UpdatePromotionData;
use Illuminate\Support\Facades\DB;

/**
 * Action for updating a promotion
 */
class UpdatePromotionAction
{
    /**
     * Update an existing promotion
     */
    public function __invoke(Promotion $promotion, UpdatePromotionData $data): Promotion
    {
        return DB::transaction(function () use ($promotion, $data) {
            $updateData = [];

            if ($data->title_ar !== null) {
                $updateData['title_ar'] = $data->title_ar;
            }
            if ($data->title_en !== null) {
                $updateData['title_en'] = $data->title_en;
            }
            if ($data->code !== null) {
                $updateData['code'] = strtoupper($data->code);
            }
            if ($data->promotion_type !== null) {
                $updateData['promotion_type'] = $data->promotion_type;
            }
            if ($data->value !== null) {
                $updateData['value'] = $data->value;
            }
            if ($data->applies_once !== null) {
                $updateData['applies_once'] = $data->applies_once;
            }
            if ($data->starts_at !== null) {
                $updateData['starts_at'] = $data->starts_at;
            }
            if ($data->ends_at !== null) {
                $updateData['ends_at'] = $data->ends_at;
            }
            if ($data->is_active !== null) {
                $updateData['is_active'] = $data->is_active;
            }
            if ($data->notes !== null) {
                $updateData['notes'] = $data->notes;
            }

            $promotion->update($updateData);

            return $promotion->fresh();
        });
    }
}

