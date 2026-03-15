<?php

namespace App\Models\Concerns;

/**
 * Trait HasCode
 *
 * Provides code/number-related helper methods for models that have code fields.
 * Applied to: Patient, Appointment, Visit, Invoice, Payment
 */
trait HasCode
{
    /**
     * Check if the model has a code assigned.
     *
     * @return bool
     */
    public function hasCode(): bool
    {
        return !empty(
            $this->patient_code
            ?? $this->appointment_no
            ?? $this->visit_no
            ?? $this->invoice_no
            ?? $this->payment_no
        );
    }

    /**
     * Get the code value for this model.
     *
     * @return string|null
     */
    public function getCode(): ?string
    {
        return $this->patient_code
            ?? $this->appointment_no
            ?? $this->visit_no
            ?? $this->invoice_no
            ?? $this->payment_no
            ?? null;
    }
}

