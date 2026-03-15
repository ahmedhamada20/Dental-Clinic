<?php

namespace App\Modules\Billing\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource for invoice detail view with all information
 */
class InvoiceDetailsResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'invoice_no' => $this->invoice_no,
            'patient' => [
                'id' => $this->patient?->id,
                'name' => $this->patient?->full_name,
                'phone' => $this->patient?->phone,
                'email' => $this->patient?->email,
            ],
            'visit' => [
                'id' => $this->visit?->id,
                'visit_no' => $this->visit?->visit_no,
                'date' => $this->visit?->visit_date?->format('Y-m-d'),
            ],
            'items' => InvoiceItemResource::collection($this->items),
            'subtotal' => (float)$this->subtotal,
            'promotion' => [
                'id' => $this->promotion?->id,
                'code' => $this->promotion?->code,
                'type' => $this->promotion?->promotion_type?->value,
                'discount_amount' => (float)$this->discount_amount,
            ],
            'total' => (float)$this->total,
            'payments' => PaymentResource::collection($this->payments),
            'paid_amount' => (float)$this->paid_amount,
            'remaining_amount' => (float)$this->remaining_amount,
            'status' => $this->status?->value,
            'issued_at' => $this->issued_at?->format('Y-m-d H:i:s'),
            'created_by' => [
                'id' => $this->createdBy?->id,
                'name' => $this->createdBy?->full_name,
            ],
            'notes' => $this->notes,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}

