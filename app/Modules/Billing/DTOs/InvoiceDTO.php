<?php

namespace App\Modules\Billing\DTOs;

class InvoiceDTO
{
    public function __construct(
        public int $patient_id,
        public array $items,
        public float $total_amount,
        public ?string $due_date = null,
        public ?string $notes = null,
    ) {}
}

