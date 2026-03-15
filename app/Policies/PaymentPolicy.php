<?php

namespace App\Policies;

use App\Models\Billing\Payment;
use App\Models\User;
use App\Policies\Concerns\InteractsWithClinicRoles;

/**
 * Class PaymentPolicy
 *
 * Authorization policy for Payment model.
 */
class PaymentPolicy
{
    use InteractsWithClinicRoles;

    /**
     * Determine if the user can view any payments.
     */
    public function viewAny(User $user): bool
    {
        return $this->hasAnyClinicRole($user, ['admin', 'receptionist']);
    }

    /**
     * Determine if the user can view the payment.
     */
    public function view(User $user, Payment $payment): bool
    {
        return $this->hasAnyClinicRole($user, ['admin', 'receptionist']);
    }

    /**
     * Determine if the user can create payments.
     */
    public function create(User $user): bool
    {
        return $this->hasAnyClinicRole($user, ['admin', 'receptionist']);
    }

    /**
     * Determine if the user can update the payment.
     */
    public function update(User $user, Payment $payment): bool
    {
        return $this->hasClinicRole($user, 'admin');
    }

    /**
     * Determine if the user can delete the payment.
     */
    public function delete(User $user, Payment $payment): bool
    {
        return $this->hasClinicRole($user, 'admin');
    }

    /**
     * Determine if the user can refund the payment.
     */
    public function refund(User $user, Payment $payment): bool
    {
        return $this->hasClinicRole($user, 'admin');
    }

    /**
     * Determine if the user can print the payment receipt.
     */
    public function printReceipt(User $user, Payment $payment): bool
    {
        return $this->hasAnyClinicRole($user, ['admin', 'receptionist']);
    }
}

