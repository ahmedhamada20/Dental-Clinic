<?php

namespace App\Modules\Appointments\Controllers;

use App\Models\Appointment\Appointment;
use App\Modules\Appointments\Actions\BookAppointmentAction;
use App\Modules\Appointments\Actions\CancelAppointmentAction;
use App\Modules\Appointments\DTOs\BookAppointmentData;
use App\Modules\Appointments\DTOs\CancelAppointmentData;
use App\Modules\Appointments\Requests\Patient\CancelAppointmentRequest;
use App\Modules\Appointments\Requests\Patient\StoreAppointmentRequest;
use App\Modules\Appointments\Resources\AppointmentDetailsResource;
use App\Modules\Appointments\Resources\AvailableSlotResource;
use App\Modules\Appointments\Services\AppointmentAvailabilityService;
use App\Support\ApiResponse;
use Illuminate\Routing\Controller;

class PatientAppointmentController extends Controller
{
    public function __construct(
        private BookAppointmentAction $bookAction,
        private CancelAppointmentAction $cancelAction,
        private AppointmentAvailabilityService $availabilityService,
    ) {}

    /**
     * Get available slots for booking.
     * GET /api/v1/patient/appointments/available-slots
     */
    public function availableSlots(): mixed
    {
        try {
            $serviceId = request()->query('service_id');
            $appointmentDate = request()->query('appointment_date');
            $doctorId = request()->query('doctor_id');

            if (!$serviceId || !$appointmentDate) {
                return ApiResponse::error('Service ID and appointment date are required', 400);
            }

            $slots = $this->availabilityService->getAvailableSlots(
                serviceId: (int)$serviceId,
                appointmentDate: $appointmentDate,
                doctorId: $doctorId ? (int)$doctorId : null
            );

            return ApiResponse::success(
                AvailableSlotResource::collection($slots),
                'Available slots retrieved successfully'
            );
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    /**
     * List patient's appointments.
     * GET /api/v1/patient/appointments
     */
    public function index(): mixed
    {
        $patient = auth()->user();
        $status = request()->query('status');
        $perPage = request()->query('per_page', 15);

        $query = Appointment::where('patient_id', $patient->id)
            ->with(['service', 'doctor', 'patient']);

        if ($status) {
            $query->where('status', $status);
        }

        $appointments = $query->paginate($perPage);

        return ApiResponse::paginated(
            $appointments,
            'Patient appointments retrieved successfully'
        );
    }

    /**
     * Book a new appointment.
     * POST /api/v1/patient/appointments
     */
    public function store(StoreAppointmentRequest $request): mixed
    {
        try {
            $patient = auth()->user();

            $data = BookAppointmentData::fromArray([
                'patient_id' => $patient->id,
                'service_id' => $request->service_id,
                'assigned_doctor_id' => $request->assigned_doctor_id,
                'appointment_date' => $request->appointment_date,
                'start_time' => $request->start_time,
                'notes' => $request->notes,
            ]);

            $appointment = ($this->bookAction)($data);

            return ApiResponse::success(
                new AppointmentDetailsResource($appointment),
                'Appointment booked successfully',
                201
            );
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    /**
     * Get appointment details.
     * GET /api/v1/patient/appointments/{id}
     */
    public function show(int $id): mixed
    {
        try {
            $patient = auth()->user();
            $appointment = Appointment::where('patient_id', $patient->id)
                ->with(['service', 'doctor', 'patient'])
                ->findOrFail($id);

            return ApiResponse::success(
                new AppointmentDetailsResource($appointment),
                'Appointment details retrieved successfully'
            );
        } catch (\Exception $e) {
            return ApiResponse::error('Appointment not found', 404);
        }
    }

    /**
     * Cancel an appointment.
     * POST /api/v1/patient/appointments/{id}/cancel
     */
    public function cancel(int $id, CancelAppointmentRequest $request): mixed
    {
        try {
            $patient = auth()->user();
            $appointment = Appointment::where('patient_id', $patient->id)
                ->findOrFail($id);

            $data = CancelAppointmentData::fromArray([
                'appointment_id' => $appointment->id,
                'cancellation_reason' => $request->cancellation_reason,
                'cancelled_by_type' => 'patient',
                'cancelled_by_id' => $patient->id,
            ]);

            $appointment = ($this->cancelAction)($data);

            return ApiResponse::success(
                new AppointmentDetailsResource($appointment),
                'Appointment cancelled successfully'
            );
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }
}

