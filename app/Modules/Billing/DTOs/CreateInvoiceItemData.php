<?php

namespace App\Modules\Billing\DTOs;

/**
 * DTO for adding an invoice item
 */
class CreateInvoiceItemData
{
    public function __construct(
        public int $invoice_id,
        public ?int $service_id = null,
        public ?int $treatment_plan_item_id = null,
        public string $item_type = 'service', // service, treatment, adjustment
        public string $item_name_ar = '',
        public string $item_name_en = '',
        public ?string $description = null,
        public float $quantity = 1,
        public float $unit_price = 0,
        public ?float $discount_amount = null,
        public ?int $tooth_number = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            invoice_id: $data['invoice_id'],
            service_id: $data['service_id'] ?? null,
            treatment_plan_item_id: $data['treatment_plan_item_id'] ?? null,
            item_type: $data['item_type'] ?? 'service',
            item_name_ar: $data['item_name_ar'] ?? '',
            item_name_en: $data['item_name_en'] ?? '',
            description: $data['description'] ?? null,
            quantity: (float)($data['quantity'] ?? 1),
            unit_price: (float)($data['unit_price'] ?? 0),
            discount_amount: isset($data['discount_amount']) ? (float)$data['discount_amount'] : null,
            tooth_number: $data['tooth_number'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'invoice_id' => $this->invoice_id,
            'service_id' => $this->service_id,
            'treatment_plan_item_id' => $this->treatment_plan_item_id,
            'item_type' => $this->item_type,
            'item_name_ar' => $this->item_name_ar,
            'item_name_en' => $this->item_name_en,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'discount_amount' => $this->discount_amount,
            'tooth_number' => $this->tooth_number,
        ];
    }
}

