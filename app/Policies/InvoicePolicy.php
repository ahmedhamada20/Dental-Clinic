<?php

namespace App\Policies;

use App\Enums\InvoiceStatus;
use App\Models\Billing\Invoice;
use App\Models\User;
use App\Policies\Concerns\InteractsWithClinicRoles;

/**
 * Class InvoicePolicy
 *
 * Authorization policy for Invoice model.
 */
class InvoicePolicy
{
    use InteractsWithClinicRoles;

    /**
     * Determine if the user can view any invoices.
     */
    public function viewAny(User $user): bool
    {
        return $this->hasAnyClinicRole($user, ['admin', 'receptionist']);
    }

    /**
     * Determine if the user can view the invoice.
     */
    public function view(User $user, Invoice $invoice): bool
    {
        return $this->hasAnyClinicRole($user, ['admin', 'doctor', 'receptionist']);
    }

    /**
     * Determine if the user can create invoices.
     */
    public function create(User $user): bool
    {
        return $this->hasAnyClinicRole($user, ['admin', 'receptionist']);
    }

    /**
     * Determine if the user can update the invoice.
     */
    public function update(User $user, Invoice $invoice): bool
    {
        // Only unpaid or partially paid invoices can be updated
        $status = $invoice->status instanceof InvoiceStatus
            ? $invoice->status->value
            : (string) $invoice->status;

        if (!in_array($status, [InvoiceStatus::UNPAID->value, InvoiceStatus::PARTIALLY_PAID->value], true)) {
            return false;
        }

        return $this->hasAnyClinicRole($user, ['admin', 'receptionist']);
    }

    /**
     * Determine if the user can delete the invoice.
     */
    public function delete(User $user, Invoice $invoice): bool
    {
        return $this->hasClinicRole($user, 'admin');
    }

    /**
     * Determine if the user can void the invoice.
     */
    public function void(User $user, Invoice $invoice): bool
    {
        return $this->hasAnyClinicRole($user, ['admin', 'receptionist']);
    }

    /**
     * Determine if the user can print the invoice.
     */
    public function print(User $user, Invoice $invoice): bool
    {
        return $this->hasAnyClinicRole($user, ['admin', 'doctor', 'receptionist']);
    }

    /**
     * Determine if the user can email the invoice.
     */
    public function email(User $user, Invoice $invoice): bool
    {
        return $this->hasAnyClinicRole($user, ['admin', 'receptionist']);
    }
}

