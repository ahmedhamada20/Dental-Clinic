<?php

namespace App\Modules\Appointments\Actions;

use App\Enums\AppointmentStatus;
use App\Enums\BookingSource;
use App\Models\Appointment\Appointment;
use App\Models\Clinic\Service;
use App\Models\User;
use App\Modules\Appointments\DTOs\BookAppointmentData;
use App\Modules\Appointments\Services\AppointmentAvailabilityService;
use App\Modules\Appointments\Services\AppointmentStatusService;

class BookAppointmentAction
{
    public function __construct(
        private AppointmentAvailabilityService $availabilityService,
        private AppointmentStatusService $statusService,
    ) {}

    /**
     * Book a new appointment.
     *
     * @throws \Exception
     */
    public function __invoke(BookAppointmentData $data): Appointment
    {
        $doctor = User::query()->findOrFail($data->doctor_id);
        $service = Service::query()->with('category:id,medical_specialty_id')->findOrFail($data->service_id);

        if ((int) $doctor->specialty_id !== $data->specialty_id) {
            throw new \InvalidArgumentException('Selected doctor must belong to the chosen specialty.');
        }

        if ((int) $service->category?->medical_specialty_id !== $data->specialty_id) {
            throw new \InvalidArgumentException('Selected service must belong to the chosen specialty.');
        }

        $duration = $service->duration_minutes ?? 30;
        $startDateTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i', "{$data->appointment_date} {$data->appointment_time}");
        $endDateTime = $startDateTime->copy()->addMinutes($duration);
        $endTime = $endDateTime->format('H:i');

        if ($this->availabilityService->hasOverlap(
            appointmentDate: $data->appointment_date,
            startTime: $data->appointment_time,
            endTime: $endTime,
            doctorId: $data->doctor_id
        )) {
            throw new \Exception('Time slot is not available');
        }

        $appointment = Appointment::create([
            'appointment_no' => $this->generateAppointmentNo(),
            'patient_id' => $data->patient_id,
            'specialty_id' => $data->specialty_id,
            'service_id' => $data->service_id,
            'assigned_doctor_id' => $data->doctor_id,
            'appointment_date' => $data->appointment_date,
            'start_time' => $data->appointment_time,
            'end_time' => $endTime,
            'status' => AppointmentStatus::PENDING,
            'booking_source' => BookingSource::WEB_APP,
            'notes' => $data->notes,
        ]);

        $this->statusService->createStatusLog(
            appointmentId: $appointment->id,
            oldStatus: null,
            newStatus: AppointmentStatus::PENDING->value,
            changedByType: 'patient',
            changedById: $data->patient_id,
            notes: 'Appointment booked online'
        );

        return $appointment->load(['specialty', 'service.category', 'doctor', 'patient']);
    }

    private function generateAppointmentNo(): string
    {
        $timestamp = now()->format('YmdHis');
        $random = random_int(1000, 9999);
        return "APT-{$timestamp}-{$random}";
    }
}

