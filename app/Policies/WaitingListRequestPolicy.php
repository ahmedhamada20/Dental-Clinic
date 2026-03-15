<?php

namespace App\Policies;

use App\Models\Appointment\WaitingListRequest;
use App\Models\User;
use App\Policies\Concerns\InteractsWithClinicRoles;

/**
 * Class WaitingListRequestPolicy
 *
 * Authorization policy for WaitingListRequest model.
 */
class WaitingListRequestPolicy
{
    use InteractsWithClinicRoles;

    /**
     * Determine if the user can view any waiting list requests.
     */
    public function viewAny(User $user): bool
    {
        return $this->hasAnyClinicRole($user, ['admin', 'receptionist']);
    }

    /**
     * Determine if the user can view the waiting list request.
     */
    public function view(User $user, WaitingListRequest $waitingListRequest): bool
    {
        return $this->hasAnyClinicRole($user, ['admin', 'receptionist']);
    }

    /**
     * Determine if the user can create waiting list requests.
     */
    public function create(User $user): bool
    {
        return $this->hasAnyClinicRole($user, ['admin', 'receptionist']);
    }

    /**
     * Determine if the user can update the waiting list request.
     */
    public function update(User $user, WaitingListRequest $waitingListRequest): bool
    {
        return $this->hasAnyClinicRole($user, ['admin', 'receptionist']);
    }

    /**
     * Determine if the user can delete the waiting list request.
     */
    public function delete(User $user, WaitingListRequest $waitingListRequest): bool
    {
        return $this->hasAnyClinicRole($user, ['admin', 'receptionist']);
    }

    /**
     * Determine if the user can approve the waiting list request.
     */
    public function approve(User $user, WaitingListRequest $waitingListRequest): bool
    {
        return $this->hasAnyClinicRole($user, ['admin', 'receptionist']);
    }

    /**
     * Determine if the user can reject the waiting list request.
     */
    public function reject(User $user, WaitingListRequest $waitingListRequest): bool
    {
        return $this->hasAnyClinicRole($user, ['admin', 'receptionist']);
    }
}

