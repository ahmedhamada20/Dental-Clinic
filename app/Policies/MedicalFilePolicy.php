<?php

namespace App\Policies;

use App\Models\Medical\MedicalFile;
use App\Models\User;
use App\Policies\Concerns\InteractsWithClinicRoles;

/**
 * Class MedicalFilePolicy
 *
 * Authorization policy for MedicalFile model.
 */
class MedicalFilePolicy
{
    use InteractsWithClinicRoles;

    /**
     * Determine if the user can view any medical files.
     */
    public function viewAny(User $user): bool
    {
        return $this->hasAnyClinicRole($user, ['admin', 'doctor', 'assistant']);
    }

    /**
     * Determine if the user can view the medical file.
     */
    public function view(User $user, MedicalFile $medicalFile): bool
    {
        return $this->hasAnyClinicRole($user, ['admin', 'doctor', 'assistant']);
    }

    /**
     * Determine if the user can create medical files.
     */
    public function create(User $user): bool
    {
        return $this->hasAnyClinicRole($user, ['admin', 'doctor', 'assistant']);
    }

    /**
     * Determine if the user can update the medical file.
     */
    public function update(User $user, MedicalFile $medicalFile): bool
    {
        // Admins can update all
        if ($this->hasClinicRole($user, 'admin')) {
            return true;
        }

        // Users can only update files they uploaded
        return $medicalFile->uploaded_by === $user->id;
    }

    /**
     * Determine if the user can delete the medical file.
     */
    public function delete(User $user, MedicalFile $medicalFile): bool
    {
        // Admins can delete all
        if ($this->hasClinicRole($user, 'admin')) {
            return true;
        }

        // Users can only delete files they uploaded
        return $medicalFile->uploaded_by === $user->id;
    }

    /**
     * Determine if the user can download the medical file.
     */
    public function download(User $user, MedicalFile $medicalFile): bool
    {
        return $this->hasAnyClinicRole($user, ['admin', 'doctor', 'assistant']);
    }
}

