<?php

namespace App\Modules\Billing\Actions;

use App\Models\Billing\Promotion;
use App\Modules\Billing\DTOs\CreatePromotionData;
use Illuminate\Support\Facades\DB;

/**
 * Action for creating a promotion
 */
class CreatePromotionAction
{
    /**
     * Create a new promotion
     */
    public function __invoke(CreatePromotionData $data): Promotion
    {
        return DB::transaction(function () use ($data) {
            $promotion = Promotion::create([
                'title_ar' => $data->title_ar,
                'title_en' => $data->title_en,
                'code' => strtoupper($data->code),
                'promotion_type' => $data->promotion_type,
                'value' => $data->value,
                'applies_once' => $data->applies_once,
                'starts_at' => $data->starts_at,
                'ends_at' => $data->ends_at,
                'is_active' => $data->is_active,
                'notes' => $data->notes,
            ]);

            return $promotion->fresh();
        });
    }
}

