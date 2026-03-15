<?php

namespace App\Policies;

use App\Models\Patient\Patient;
use App\Models\User;
use App\Policies\Concerns\InteractsWithClinicRoles;

/**
 * Class PatientPolicy
 *
 * Authorization policy for Patient model.
 * Controls access to patient records based on user roles.
 */
class PatientPolicy
{
    use InteractsWithClinicRoles;

    /**
     * Determine if the user can view any patients.
     */
    public function viewAny(User $user): bool
    {
        return $this->hasAnyClinicRole($user, ['admin', 'doctor', 'receptionist', 'assistant']);
    }

    /**
     * Determine if the user can view the patient.
     */
    public function view(User $user, Patient $patient): bool
    {
        return $this->hasAnyClinicRole($user, ['admin', 'doctor', 'receptionist', 'assistant']);
    }

    /**
     * Determine if the user can create patients.
     */
    public function create(User $user): bool
    {
        return $this->hasAnyClinicRole($user, ['admin', 'receptionist']);
    }

    /**
     * Determine if the user can update the patient.
     */
    public function update(User $user, Patient $patient): bool
    {
        return $this->hasAnyClinicRole($user, ['admin', 'receptionist']);
    }

    /**
     * Determine if the user can delete the patient.
     */
    public function delete(User $user, Patient $patient): bool
    {
        return $this->hasClinicRole($user, 'admin');
    }

    /**
     * Determine if the user can restore the patient.
     */
    public function restore(User $user, Patient $patient): bool
    {
        return $this->hasClinicRole($user, 'admin');
    }

    /**
     * Determine if the user can permanently delete the patient.
     */
    public function forceDelete(User $user, Patient $patient): bool
    {
        return $this->hasClinicRole($user, 'admin');
    }

    /**
     * Determine if the user can view patient medical history.
     */
    public function viewMedicalHistory(User $user, Patient $patient): bool
    {
        return $this->hasAnyClinicRole($user, ['admin', 'doctor', 'assistant']);
    }

    /**
     * Determine if the user can update patient medical history.
     */
    public function updateMedicalHistory(User $user, Patient $patient): bool
    {
        return $this->hasAnyClinicRole($user, ['admin', 'doctor', 'assistant']);
    }
}

