<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Visit\VisitNote;
use App\Policies\Concerns\InteractsWithClinicRoles;

/**
 * Class VisitNotePolicy
 *
 * Authorization policy for VisitNote model.
 */
class VisitNotePolicy
{
    use InteractsWithClinicRoles;

    /**
     * Determine if the user can view any visit notes.
     */
    public function viewAny(User $user): bool
    {
        return $this->hasAnyClinicRole($user, ['admin', 'doctor', 'assistant']);
    }

    /**
     * Determine if the user can view the visit note.
     */
    public function view(User $user, VisitNote $visitNote): bool
    {
        // Admins can view all
        if ($this->hasClinicRole($user, 'admin')) {
            return true;
        }

        if ($this->hasClinicRole($user, 'doctor')) {
            return $visitNote->visit->doctor_id === $user->id;
        }

        // Assistants can view notes they authored while supporting visits.
        return $this->hasClinicRole($user, 'assistant') && $visitNote->created_by === $user->id;
    }

    /**
     * Determine if the user can create visit notes.
     */
    public function create(User $user): bool
    {
        return $this->hasAnyClinicRole($user, ['admin', 'doctor', 'assistant']);
    }

    /**
     * Determine if the user can update the visit note.
     */
    public function update(User $user, VisitNote $visitNote): bool
    {
        // Admins can update all
        if ($this->hasClinicRole($user, 'admin')) {
            return true;
        }

        // Users can only update their own notes
        return $visitNote->created_by === $user->id;
    }

    /**
     * Determine if the user can delete the visit note.
     */
    public function delete(User $user, VisitNote $visitNote): bool
    {
        // Admins can delete all
        if ($this->hasClinicRole($user, 'admin')) {
            return true;
        }

        // Users can only delete their own notes
        return $visitNote->created_by === $user->id;
    }
}
