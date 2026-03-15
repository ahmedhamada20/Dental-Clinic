<?php

namespace App\Modules\Appointments\Services;

use App\Models\Appointment\Appointment;
use App\Models\Clinic\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AppointmentAvailabilityService
{
    /**
     * Get available slots for a given date and service.
     *
     * @param int $serviceId
     * @param string $appointmentDate (Y-m-d format)
     * @param ?int $doctorId
     * @return Collection
     */
    public function getAvailableSlots(int $specialtyId, int $serviceId, string $appointmentDate, ?int $doctorId = null): Collection
    {
        $service = Service::query()->with('category:id,medical_specialty_id')->findOrFail($serviceId);

        if ((int) $service->category?->medical_specialty_id !== $specialtyId) {
            throw new \InvalidArgumentException('Selected service must belong to the chosen specialty.');
        }

        if ($doctorId !== null) {
            $doctor = User::query()->findOrFail($doctorId);

            if ((int) $doctor->specialty_id !== $specialtyId) {
                throw new \InvalidArgumentException('Selected doctor must belong to the chosen specialty.');
            }
        }

        $duration = $service->duration_minutes ?? 30;
        $date = Carbon::createFromFormat('Y-m-d', $appointmentDate);

        // Define clinic working hours (9 AM to 6 PM)
        $startHour = 9;
        $endHour = 18;

        $slots = collect();
        $currentTime = $date->copy()->setTime($startHour, 0);
        $endTime = $date->copy()->setTime($endHour, 0);

        while ($currentTime->isBefore($endTime)) {
            $slotEnd = $currentTime->copy()->addMinutes($duration);

            if ($slotEnd->isAfter($endTime)) {
                break;
            }

            $isAvailable = $this->isSlotAvailable(
                appointmentDate: $appointmentDate,
                startTime: $currentTime->format('H:i'),
                endTime: $slotEnd->format('H:i'),
                doctorId: $doctorId
            );

            $slots->push([
                'specialty_id' => $specialtyId,
                'doctor_id' => $doctorId,
                'service_id' => $serviceId,
                'appointment_date' => $appointmentDate,
                'start_time' => $currentTime->format('H:i'),
                'end_time' => $slotEnd->format('H:i'),
                'duration_minutes' => $duration,
                'is_available' => $isAvailable,
            ]);

            $currentTime->addMinutes(15); // 15-minute intervals
        }

        return $slots;
    }

    /**
     * Check if a specific slot is available.
     */
    private function isSlotAvailable(
        string $appointmentDate,
        string $startTime,
        string $endTime,
        ?int $doctorId = null
    ): bool
    {
        $query = Appointment::where('appointment_date', $appointmentDate)
            ->whereIn('status', ['pending', 'confirmed'])
            ->where(function ($q) use ($startTime, $endTime) {
                $q->whereBetween('start_time', [$startTime, $endTime])
                    ->orWhereBetween('end_time', [$startTime, $endTime])
                    ->orWhere(function ($q2) use ($startTime, $endTime) {
                        $q2->where('start_time', '<=', $startTime)
                            ->where('end_time', '>=', $endTime);
                    });
            });

        if ($doctorId) {
            $query->where('assigned_doctor_id', $doctorId);
        }

        return $query->count() === 0;
    }

    /**
     * Check if appointment overlaps with existing appointments.
     */
    public function hasOverlap(
        string $appointmentDate,
        string $startTime,
        string $endTime,
        ?int $doctorId = null,
        ?int $excludeAppointmentId = null
    ): bool
    {
        $query = Appointment::where('appointment_date', $appointmentDate)
            ->whereIn('status', ['pending', 'confirmed'])
            ->where(function ($q) use ($startTime, $endTime) {
                $q->whereBetween('start_time', [$startTime, $endTime])
                    ->orWhereBetween('end_time', [$startTime, $endTime])
                    ->orWhere(function ($q2) use ($startTime, $endTime) {
                        $q2->where('start_time', '<=', $startTime)
                            ->where('end_time', '>=', $endTime);
                    });
            });

        if ($doctorId) {
            $query->where('assigned_doctor_id', $doctorId);
        }

        if ($excludeAppointmentId) {
            $query->where('id', '!=', $excludeAppointmentId);
        }

        return $query->exists();
    }
}

