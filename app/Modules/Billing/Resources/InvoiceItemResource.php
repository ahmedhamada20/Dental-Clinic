<?php

namespace App\Modules\Billing\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource for individual invoice items
 */
class InvoiceItemResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'invoice_id' => $this->invoice_id,
            'service' => [
                'id' => $this->service?->id,
                'name_ar' => $this->service?->name_ar,
                'name_en' => $this->service?->name_en,
            ],
            'item_type' => $this->item_type,
            'item_name' => [
                'ar' => $this->item_name_ar,
                'en' => $this->item_name_en,
            ],
            'description' => $this->description,
            'quantity' => (float)$this->quantity,
            'unit_price' => (float)$this->unit_price,
            'discount_amount' => (float)($this->discount_amount ?? 0),
            'total' => (float)$this->total,
            'tooth_number' => $this->tooth_number,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}

