<?php

namespace App\Modules\Appointments\Controllers\Patient;

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

class AppointmentController extends Controller
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
            $specialtyId = request()->query('specialty_id');
            $doctorId = request()->query('doctor_id');
            $serviceId = request()->query('service_id');
            $appointmentDate = request()->query('appointment_date');

            if (!$specialtyId || !$doctorId || !$serviceId || !$appointmentDate) {
                return ApiResponse::error('Specialty ID, doctor ID, service ID, and appointment date are required', 400);
            }

            $slots = $this->availabilityService->getAvailableSlots(
                (int) $specialtyId,
                (int) $serviceId,
                $appointmentDate,
                (int) $doctorId
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
            ->with(['specialty', 'service.category', 'doctor', 'patient']);

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
                'specialty_id' => $request->integer('specialty_id'),
                'doctor_id' => $request->integer('doctor_id'),
                'service_id' => $request->integer('service_id'),
                'appointment_date' => $request->appointment_date,
                'appointment_time' => $request->appointment_time,
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
                ->with(['specialty', 'service.category', 'doctor', 'patient'])
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

