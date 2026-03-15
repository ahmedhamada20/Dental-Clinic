<?php

namespace App\Modules\Billing\DTOs;

/**
 * DTO for creating an invoice from a visit
 */
class CreateInvoiceData
{
    public function __construct(
        public int $patient_id,
        public int $created_by,
        public ?int $visit_id = null,
        public ?int $promotion_id = null,
        public ?string $notes = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            patient_id: $data['patient_id'],
            created_by: $data['created_by'],
            visit_id: $data['visit_id'] ?? null,
            promotion_id: $data['promotion_id'] ?? null,
            notes: $data['notes'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'patient_id' => $this->patient_id,
            'created_by' => $this->created_by,
            'visit_id' => $this->visit_id,
            'promotion_id' => $this->promotion_id,
            'notes' => $this->notes,
        ];
    }
}

