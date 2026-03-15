<?php

namespace App\Modules\Billing\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource for invoice list view (compact format)
 */
class InvoiceResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'invoice_no' => $this->invoice_no,
            'patient' => [
                'id' => $this->patient?->id,
                'name' => $this->patient?->full_name,
            ],
            'subtotal' => (float)$this->subtotal,
            'discount_amount' => (float)$this->discount_amount,
            'total' => (float)$this->total,
            'paid_amount' => (float)$this->paid_amount,
            'remaining_amount' => (float)$this->remaining_amount,
            'status' => $this->status?->value,
            'issued_at' => $this->issued_at?->format('Y-m-d'),
            'payment_count' => $this->payments_count ?? $this->payments()->count(),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}

