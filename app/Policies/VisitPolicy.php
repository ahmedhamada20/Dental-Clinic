<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Visit\Visit;
use App\Policies\Concerns\InteractsWithClinicRoles;

/**
 * Class VisitPolicy
 *
 * Authorization policy for Visit model.
 */
class VisitPolicy
{
    use InteractsWithClinicRoles;

    /**
     * Determine if the user can view any visits.
     */
    public function viewAny(User $user): bool
    {
        return $this->hasAnyClinicRole($user, ['admin', 'doctor', 'receptionist', 'assistant']);
    }

    /**
     * Determine if the user can view the visit.
     */
    public function view(User $user, Visit $visit): bool
    {
        // Admins and receptionists can view all
        if ($this->hasAnyClinicRole($user, ['admin', 'receptionist', 'assistant'])) {
            return true;
        }

        // Doctors can view their own visits
        return $this->hasClinicRole($user, 'doctor') && $visit->doctor_id === $user->id;
    }

    /**
     * Determine if the user can create visits.
     */
    public function create(User $user): bool
    {
        return $this->hasAnyClinicRole($user, ['admin', 'doctor', 'receptionist']);
    }

    /**
     * Determine if the user can update the visit.
     */
    public function update(User $user, Visit $visit): bool
    {
        // Admins can update all
        if ($this->hasClinicRole($user, 'admin')) {
            return true;
        }

        // Doctors can only update their own visits
        return $this->hasClinicRole($user, 'doctor') && $visit->doctor_id === $user->id;
    }

    /**
     * Determine if the user can delete the visit.
     */
    public function delete(User $user, Visit $visit): bool
    {
        return $this->hasClinicRole($user, 'admin');
    }

    /**
     * Determine if the user can complete the visit.
     */
    public function complete(User $user, Visit $visit): bool
    {
        // Only the assigned doctor or admin can complete
        return $this->hasClinicRole($user, 'admin')
            || ($this->hasClinicRole($user, 'doctor') && $visit->doctor_id === $user->id);
    }
}

