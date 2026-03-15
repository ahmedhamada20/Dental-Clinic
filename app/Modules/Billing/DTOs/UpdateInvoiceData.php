<?php

namespace App\Modules\Billing\DTOs;

/**
 * DTO for updating an invoice
 */
class UpdateInvoiceData
{
    public function __construct(
        public ?int $patient_id = null,
        public ?int $visit_id = null,
        public ?int $promotion_id = null,
        public ?string $notes = null,
        public ?string $discount_type = null,
        public ?float $discount_value = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            patient_id: $data['patient_id'] ?? null,
            visit_id: $data['visit_id'] ?? null,
            promotion_id: $data['promotion_id'] ?? null,
            notes: $data['notes'] ?? null,
            discount_type: $data['discount_type'] ?? null,
            discount_value: isset($data['discount_value']) ? (float) $data['discount_value'] : null,
        );
    }

    public function toArray(): array
    {
        return [
            'patient_id' => $this->patient_id,
            'visit_id' => $this->visit_id,
            'promotion_id' => $this->promotion_id,
            'notes' => $this->notes,
            'discount_type' => $this->discount_type,
            'discount_value' => $this->discount_value,
        ];
    }
}
