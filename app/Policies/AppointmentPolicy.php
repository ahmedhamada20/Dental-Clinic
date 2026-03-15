<?php

namespace App\Policies;

use App\Policies\Concerns\InteractsWithClinicRoles;
use App\Models\Appointment\Appointment;
use App\Models\User;

/**
 * Class AppointmentPolicy
 *
 * Authorization policy for Appointment model.
 */
class AppointmentPolicy
{
    use InteractsWithClinicRoles;

    /**
     * Determine if the user can view any appointments.
     */
    public function viewAny(User $user): bool
    {
        return $this->hasAnyClinicRole($user, ['admin', 'doctor', 'receptionist']);
    }

    /**
     * Determine if the user can view the appointment.
     */
    public function view(User $user, Appointment $appointment): bool
    {
        return $this->hasAnyClinicRole($user, ['admin', 'doctor', 'receptionist']);
    }

    /**
     * Determine if the user can create appointments.
     */
    public function create(User $user): bool
    {
        return $this->hasAnyClinicRole($user, ['admin', 'receptionist']);
    }

    /**
     * Determine if the user can update the appointment.
     */
    public function update(User $user, Appointment $appointment): bool
    {
        // Receptionists and admins can update
        // Doctors can only update their assigned appointments
        if ($this->hasAnyClinicRole($user, ['admin', 'receptionist'])) {
            return true;
        }

        return $this->hasClinicRole($user, 'doctor')
            && $appointment->assigned_doctor_id === $user->id;
    }

    /**
     * Determine if the user can delete the appointment.
     */
    public function delete(User $user, Appointment $appointment): bool
    {
        return $this->hasAnyClinicRole($user, ['admin', 'receptionist']);
    }

    /**
     * Determine if the user can confirm the appointment.
     */
    public function confirm(User $user, Appointment $appointment): bool
    {
        return $this->hasAnyClinicRole($user, ['admin', 'receptionist', 'doctor']);
    }

    /**
     * Determine if the user can cancel the appointment.
     */
    public function cancel(User $user, Appointment $appointment): bool
    {
        return $this->hasAnyClinicRole($user, ['admin', 'receptionist', 'doctor']);
    }

    /**
     * Determine if the user can reschedule the appointment.
     */
    public function reschedule(User $user, Appointment $appointment): bool
    {
        return $this->hasAnyClinicRole($user, ['admin', 'receptionist']);
    }
}

