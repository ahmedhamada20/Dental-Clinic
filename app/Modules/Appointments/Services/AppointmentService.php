<?php

namespace App\Modules\Appointments\Services;

use App\Enums\AppointmentStatus;
use App\Models\Appointment\Appointment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class AppointmentService
{
    /**
     * Create an appointment.
     */
    public function createAppointment(array $data): Appointment
    {
        return Appointment::create($data);
    }

    /**
     * Update an appointment.
     */
    public function updateAppointment(int $id, array $data): Appointment
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->update($data);
        return $appointment;
    }

    /**
     * Get a single appointment.
     */
    public function getAppointment(int $id): Appointment
    {
        return Appointment::with(['service', 'doctor', 'patient', 'statusLogs'])
            ->findOrFail($id);
    }

    /**
     * Get appointments with filters and pagination.
     */
    public function getAppointmentsWithFilters(
        int $page = 1,
        int $perPage = 15,
        ?string $status = null,
        ?int $patientId = null,
        ?int $doctorId = null,
        ?string $startDate = null,
        ?string $endDate = null,
        string $sortBy = 'appointment_date',
        string $sortDirection = 'desc'
    ): LengthAwarePaginator {
        $query = Appointment::query()->with(['service', 'doctor', 'patient']);
        $allowedSortBy = ['appointment_date', 'start_time', 'status', 'created_at'];
        $resolvedSortBy = in_array($sortBy, $allowedSortBy, true) ? $sortBy : 'appointment_date';
        $resolvedSortDirection = strtolower($sortDirection) === 'asc' ? 'asc' : 'desc';

        if ($status) {
            $query->where('status', $status);
        }

        if ($patientId) {
            $query->where('patient_id', $patientId);
        }

        if ($doctorId) {
            $query->where('assigned_doctor_id', $doctorId);
        }

        if ($startDate) {
            $query->whereDate('appointment_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('appointment_date', '<=', $endDate);
        }

        return $query->orderBy($resolvedSortBy, $resolvedSortDirection)
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Get patient appointments.
     */
    public function getPatientAppointments(int $patientId, int $perPage = 15): LengthAwarePaginator
    {
        return Appointment::query()->where('patient_id', $patientId)
            ->with(['service', 'doctor', 'patient'])
            ->orderBy('appointment_date', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get upcoming appointments for a patient.
     */
    public function getUpcomingAppointments(int $patientId): Collection
    {
        return Appointment::query()->where('patient_id', $patientId)
            ->upcoming()
            ->with(['service', 'doctor'])
            ->get();
    }

    /**
     * Get today's appointments.
     */
    public function getTodayAppointments(): Collection
    {
        return Appointment::query()->whereDate('appointment_date', now())
            ->with(['service', 'doctor', 'patient'])
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Cancel an appointment.
     */
    public function cancelAppointment(int $id, string $reason): Appointment
    {
        $appointment = Appointment::findOrFail($id);

        $appointment->update([
            'status' => AppointmentStatus::CANCELLED_BY_ADMIN,
            'cancellation_reason' => $reason,
            'cancelled_at' => now(),
        ]);

        return $appointment;
    }
}

