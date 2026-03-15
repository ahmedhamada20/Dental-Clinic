<?php

namespace App\Policies;

use App\Models\User;
use App\Policies\Concerns\InteractsWithClinicRoles;

/**
 * Class OdontogramPolicy
 *
 * Authorization policy for Odontogram (OdontogramTooth and OdontogramHistory).
 */
class OdontogramPolicy
{
    use InteractsWithClinicRoles;

    protected function canManage(User $user): bool
    {
        return $this->hasAnyClinicRole($user, ['admin', 'doctor']);
    }

    /**
     * Determine if the user can view any odontogram records.
     */
    public function viewAny(User $user): bool
    {
        return $this->canManage($user);
    }

    /**
     * Determine if the user can view the odontogram.
     */
    public function view(User $user): bool
    {
        return $this->canManage($user);
    }

    /**
     * Determine if the user can create odontogram records.
     */
    public function create(User $user): bool
    {
        return $this->canManage($user);
    }

    /**
     * Determine if the user can update odontogram records.
     */
    public function update(User $user): bool
    {
        return $this->canManage($user);
    }

    /**
     * Determine if the user can delete odontogram records.
     */
    public function delete(User $user): bool
    {
        return $this->hasClinicRole($user, 'admin');
    }

    /**
     * Determine if the user can view odontogram history.
     */
    public function viewHistory(User $user): bool
    {
        return $this->canManage($user);
    }
}
