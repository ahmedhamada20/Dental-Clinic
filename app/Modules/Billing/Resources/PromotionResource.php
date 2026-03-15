<?php

namespace App\Modules\Billing\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource for promotion list and detail views
 */
class PromotionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'title' => [
                'ar' => $this->title_ar,
                'en' => $this->title_en,
            ],
            'promotion_type' => $this->promotion_type?->value,
            'value' => (float)$this->value,
            'applies_once' => (bool)$this->applies_once,
            'is_active' => (bool)$this->is_active,
            'valid_period' => [
                'starts_at' => $this->starts_at?->format('Y-m-d'),
                'ends_at' => $this->ends_at?->format('Y-m-d'),
            ],
            'notes' => $this->notes,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}

