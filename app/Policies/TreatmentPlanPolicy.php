<?php

namespace App\Policies;

use App\Models\Medical\TreatmentPlan;
use App\Models\User;
use App\Policies\Concerns\InteractsWithClinicRoles;

/**
 * Class TreatmentPlanPolicy
 *
 * Authorization policy for TreatmentPlan model.
 */
class TreatmentPlanPolicy
{
    use InteractsWithClinicRoles;

    /**
     * Determine if the user can view any treatment plans.
     */
    public function viewAny(User $user): bool
    {
        return $this->hasAnyClinicRole($user, ['admin', 'doctor']);
    }

    /**
     * Determine if the user can view the treatment plan.
     */
    public function view(User $user, TreatmentPlan $treatmentPlan): bool
    {
        // Admins can view all
        if ($this->hasClinicRole($user, 'admin')) {
            return true;
        }

        // Doctors can view their own treatment plans
        return $this->hasClinicRole($user, 'doctor') && $treatmentPlan->doctor_id === $user->id;
    }

    /**
     * Determine if the user can create treatment plans.
     */
    public function create(User $user): bool
    {
        return $this->hasAnyClinicRole($user, ['admin', 'doctor']);
    }

    /**
     * Determine if the user can update the treatment plan.
     */
    public function update(User $user, TreatmentPlan $treatmentPlan): bool
    {
        // Admins can update all
        if ($this->hasClinicRole($user, 'admin')) {
            return true;
        }

        // Doctors can only update their own treatment plans
        return $this->hasClinicRole($user, 'doctor') && $treatmentPlan->doctor_id === $user->id;
    }

    /**
     * Determine if the user can delete the treatment plan.
     */
    public function delete(User $user, TreatmentPlan $treatmentPlan): bool
    {
        return $this->hasClinicRole($user, 'admin');
    }

    /**
     * Determine if the user can approve the treatment plan.
     */
    public function approve(User $user, TreatmentPlan $treatmentPlan): bool
    {
        return $this->hasClinicRole($user, 'admin');
    }

    /**
     * Determine if the user can complete the treatment plan.
     */
    public function complete(User $user, TreatmentPlan $treatmentPlan): bool
    {
        // Only the assigned doctor or admin can complete
        return $this->hasClinicRole($user, 'admin')
            || ($this->hasClinicRole($user, 'doctor') && $treatmentPlan->doctor_id === $user->id);
    }
}

