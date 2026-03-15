<?php

namespace App\Modules\Billing\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource for payment records
 */
class PaymentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'payment_no' => $this->payment_no,
            'invoice_id' => $this->invoice_id,
            'invoice_no' => $this->invoice?->invoice_no,
            'patient' => [
                'id' => $this->patient?->id,
                'name' => $this->patient?->full_name,
            ],
            'payment_method' => $this->payment_method?->value,
            'amount' => (float)$this->amount,
            'reference_no' => $this->reference_no,
            'payment_date' => $this->payment_date?->format('Y-m-d H:i:s'),
            'received_by' => [
                'id' => $this->receivedBy?->id,
                'name' => $this->receivedBy?->full_name,
            ],
            'notes' => $this->notes,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}

