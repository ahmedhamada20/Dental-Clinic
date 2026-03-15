<?php

namespace App\Models\Concerns;

/**
 * Trait HasStatus
 *
 * Provides status-related helper methods for models that track status field.
 * Applied to: Appointment, Visit, Invoice, TreatmentPlan, TreatmentPlanItem, WaitingListRequest, VisitTicket
 */
trait HasStatus
{
    /**
     * Check if the model has a specific status.
     *
     * @param string $status
     * @return bool
     */
    public function isStatus(string $status): bool
    {
        return $this->status === $status;
    }

    /**
     * Check if model is active/pending.
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->isStatus('pending');
    }

    /**
     * Check if model is confirmed.
     *
     * @return bool
     */
    public function isConfirmed(): bool
    {
        return $this->isStatus('confirmed');
    }

    /**
     * Check if model is completed.
     *
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->isStatus('completed');
    }

    /**
     * Check if model is cancelled.
     *
     * @return bool
     */
    public function isCancelled(): bool
    {
        return $this->isStatus('cancelled');
    }
}

