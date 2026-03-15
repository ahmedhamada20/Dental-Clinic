<?php

namespace App\Policies;

use App\Models\Medical\Prescription;
use App\Models\User;
use App\Policies\Concerns\InteractsWithClinicRoles;

/**
 * Class PrescriptionPolicy
 *
 * Authorization policy for Prescription model.
 */
class PrescriptionPolicy
{
    use InteractsWithClinicRoles;

    /**
     * Determine if the user can view any prescriptions.
     */
    public function viewAny(User $user): bool
    {
        return $this->hasAnyClinicRole($user, ['admin', 'doctor']);
    }

    /**
     * Determine if the user can view the prescription.
     */
    public function view(User $user, Prescription $prescription): bool
    {
        // Admins can view all
        if ($this->hasClinicRole($user, 'admin')) {
            return true;
        }

        // Doctors can view their own prescriptions
        return $this->hasClinicRole($user, 'doctor') && $prescription->doctor_id === $user->id;
    }

    /**
     * Determine if the user can create prescriptions.
     */
    public function create(User $user): bool
    {
        return $this->hasAnyClinicRole($user, ['admin', 'doctor']);
    }

    /**
     * Determine if the user can update the prescription.
     */
    public function update(User $user, Prescription $prescription): bool
    {
        // Admins can update all
        if ($this->hasClinicRole($user, 'admin')) {
            return true;
        }

        // Doctors can only update their own prescriptions
        return $this->hasClinicRole($user, 'doctor') && $prescription->doctor_id === $user->id;
    }

    /**
     * Determine if the user can delete the prescription.
     */
    public function delete(User $user, Prescription $prescription): bool
    {
        // Admins can delete all
        if ($this->hasClinicRole($user, 'admin')) {
            return true;
        }

        // Doctors can only delete their own prescriptions
        return $this->hasClinicRole($user, 'doctor') && $prescription->doctor_id === $user->id;
    }

    /**
     * Determine if the user can print the prescription.
     */
    public function print(User $user, Prescription $prescription): bool
    {
        return $this->hasAnyClinicRole($user, ['admin', 'doctor']);
    }
}

