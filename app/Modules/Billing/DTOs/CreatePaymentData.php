<?php

namespace App\Modules\Billing\DTOs;

/**
 * DTO for recording a payment
 */
class CreatePaymentData
{
    public function __construct(
        public int $patient_id,
        public int $received_by,
        public int $invoice_id,
        public string $payment_method, // cash, card, cheque, transfer, instapay, etc
        public float $amount,
        public ?string $reference_no = null,
        public ?string $notes = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            patient_id: $data['patient_id'],
            received_by: $data['received_by'],
            invoice_id: $data['invoice_id'],
            payment_method: $data['payment_method'],
            amount: (float)$data['amount'],
            reference_no: $data['reference_no'] ?? null,
            notes: $data['notes'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'patient_id' => $this->patient_id,
            'received_by' => $this->received_by,
            'invoice_id' => $this->invoice_id,
            'payment_method' => $this->payment_method,
            'amount' => $this->amount,
            'reference_no' => $this->reference_no,
            'notes' => $this->notes,
        ];
    }
}

