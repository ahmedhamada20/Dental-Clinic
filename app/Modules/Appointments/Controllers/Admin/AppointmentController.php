<?php

namespace App\Modules\Appointments\Controllers\Admin;

use App\Models\Appointment\Appointment;
use App\Models\Clinic\Service;
use App\Modules\Appointments\Actions\BookAppointmentAction;
use App\Modules\Appointments\Actions\CancelAppointmentAction;
use App\Modules\Appointments\Actions\CheckInAppointmentAction;
use App\Modules\Appointments\Actions\ConfirmAppointmentAction;
use App\Modules\Appointments\Actions\MarkAppointmentNoShowAction;
use App\Modules\Appointments\DTOs\BookAppointmentData;
use App\Modules\Appointments\DTOs\CancelAppointmentData;
use App\Modules\Appointments\Requests\Admin\CancelAppointmentRequest;
use App\Modules\Appointments\Requests\Admin\CheckInAppointmentRequest;
use App\Modules\Appointments\Requests\Admin\ConfirmAppointmentRequest;
use App\Modules\Appointments\Requests\Admin\StoreAppointmentRequest;
use App\Modules\Appointments\Requests\Admin\UpdateAppointmentRequest;
use App\Modules\Appointments\Resources\AppointmentDetailsResource;
use App\Modules\Appointments\Resources\AppointmentStatusLogResource;
use App\Modules\Appointments\Services\AppointmentStatusService;
use App\Support\ApiResponse;
use Carbon\Carbon;
use Illuminate\Routing\Controller;

class AppointmentController extends Controller
{
    public function __construct(
        private BookAppointmentAction $bookAction,
        private CancelAppointmentAction $cancelAction,
        private ConfirmAppointmentAction $confirmAction,
        private CheckInAppointmentAction $checkInAction,
        private MarkAppointmentNoShowAction $noShowAction,
        private AppointmentStatusService $statusService,
    ) {}

    /**
     * List all appointments.
     * GET /api/v1/admin/appointments
     */
    public function index(): mixed
    {
        try {
            $status = request()->query('status');
            $patientId = request()->query('patient_id');
            $doctorId = request()->query('doctor_id');
            $specialtyId = request()->query('specialty_id');
            $startDate = request()->query('start_date');
            $endDate = request()->query('end_date');
            $perPage = request()->query('per_page', 15);

            $query = Appointment::with(['specialty', 'service.category', 'doctor', 'patient']);

            if ($status) {
                $query->where('status', $status);
            }

            if ($patientId) {
                $query->where('patient_id', (int) $patientId);
            }

            if ($doctorId) {
                $query->where('assigned_doctor_id', (int) $doctorId);
            }

            if ($specialtyId) {
                $query->where('specialty_id', (int) $specialtyId);
            }

            if ($startDate) {
                $query->whereDate('appointment_date', '>=', $startDate);
            }

            if ($endDate) {
                $query->whereDate('appointment_date', '<=', $endDate);
            }

            $appointments = $query->orderBy('appointment_date', 'desc')->paginate($perPage);

            return ApiResponse::paginated($appointments, 'Appointments retrieved successfully');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    /**
     * Create appointment as admin.
     * POST /api/v1/admin/appointments
     */
    public function store(StoreAppointmentRequest $request): mixed
    {
        try {
            $data = BookAppointmentData::fromArray([
                'patient_id' => $request->integer('patient_id'),
                'specialty_id' => $request->integer('specialty_id'),
                'doctor_id' => $request->integer('doctor_id'),
                'service_id' => $request->integer('service_id'),
                'appointment_date' => $request->appointment_date,
                'appointment_time' => $request->appointment_time,
                'notes' => $request->notes,
            ]);

            $appointment = ($this->bookAction)($data);

            return ApiResponse::success(new AppointmentDetailsResource($appointment), 'Appointment created successfully', 201);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    /**
     * Get appointment details.
     * GET /api/v1/admin/appointments/{id}
     */
    public function show(int $id): mixed
    {
        try {
            $appointment = Appointment::with(['specialty', 'service.category', 'doctor', 'patient', 'statusLogs'])
                ->findOrFail($id);

            return ApiResponse::success(new AppointmentDetailsResource($appointment), 'Appointment details retrieved successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Appointment not found', 404);
        }
    }

    /**
     * Update appointment.
     * PUT /api/v1/admin/appointments/{id}
     */
    public function update(int $id, UpdateAppointmentRequest $request): mixed
    {
        try {
            $appointment = Appointment::with('service.category')->findOrFail($id);

            $specialtyId = $request->has('specialty_id')
                ? $request->integer('specialty_id')
                : (int) $appointment->specialty_id;
            $serviceId = $request->has('service_id')
                ? $request->integer('service_id')
                : (int) $appointment->service_id;
            $doctorId = $request->has('doctor_id')
                ? $request->integer('doctor_id')
                : (int) $appointment->assigned_doctor_id;
            $appointmentDate = $request->appointment_date ?? $appointment->appointment_date?->format('Y-m-d');
            $appointmentTime = $request->appointment_time ?? Carbon::parse($appointment->start_time)->format('H:i');

            $service = Service::query()->findOrFail($serviceId);
            $duration = $service->duration_minutes ?? 30;
            $startDateTime = Carbon::createFromFormat('Y-m-d H:i', "{$appointmentDate} {$appointmentTime}");
            $endTime = $startDateTime->copy()->addMinutes($duration)->format('H:i');

            $updateData = array_filter([
                'specialty_id' => $specialtyId,
                'service_id' => $serviceId,
                'assigned_doctor_id' => $doctorId,
                'appointment_date' => $appointmentDate,
                'start_time' => $appointmentTime,
                'end_time' => $endTime,
                'status' => $request->status,
                'notes' => $request->notes,
            ], fn ($value) => $value !== null);

            $appointment->update($updateData);
            $appointment->load(['specialty', 'service.category', 'doctor', 'patient']);

            return ApiResponse::success(new AppointmentDetailsResource($appointment), 'Appointment updated successfully');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    /**
     * Confirm appointment.
     * POST /api/v1/admin/appointments/{id}/confirm
     */
    public function confirm(int $id, ConfirmAppointmentRequest $request): mixed
    {
        try {
            $admin = auth()->user();
            $appointment = ($this->confirmAction)(
                $id,
                $admin->id,
                $request->notes ?? null
            );

            return ApiResponse::success(
                new AppointmentDetailsResource($appointment),
                'Appointment confirmed successfully'
            );
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    /**
     * Check in appointment.
     * POST /api/v1/admin/appointments/{id}/check-in
     */
    public function checkIn(int $id, CheckInAppointmentRequest $request): mixed
    {
        try {
            $admin = auth()->user();
            $result = ($this->checkInAction)(
                $id,
                $admin->id,
                $request->notes ?? null
            );

            return ApiResponse::success([
                'appointment' => new AppointmentDetailsResource($result['appointment']),
                'visit' => $result['visit'],
                'ticket' => $result['ticket'],
            ], 'Appointment checked in successfully');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    /**
     * Mark appointment as no-show.
     * POST /api/v1/admin/appointments/{id}/mark-no-show
     */
    public function markNoShow(int $id): mixed
    {
        try {
            $admin = auth()->user();
            $appointment = ($this->noShowAction)(
                $id,
                $admin->id,
                'Patient did not show up'
            );

            return ApiResponse::success(
                new AppointmentDetailsResource($appointment),
                'Appointment marked as no-show'
            );
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    /**
     * Cancel appointment as admin.
     * POST /api/v1/admin/appointments/{id}/cancel
     */
    public function cancel(int $id, CancelAppointmentRequest $request): mixed
    {
        try {
            $admin = auth()->user();

            $data = CancelAppointmentData::fromArray([
                'appointment_id' => $id,
                'cancellation_reason' => $request->cancellation_reason,
                'cancelled_by_type' => 'user',
                'cancelled_by_id' => $admin->id,
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

    /**
     * Get appointment status logs.
     * GET /api/v1/admin/appointments/{id}/status-logs
     */
    public function statusLogs(int $id): mixed
    {
        try {
            $appointment = Appointment::findOrFail($id);
            $logs = $this->statusService->getStatusLogs($id);

            return ApiResponse::success(
                AppointmentStatusLogResource::collection($logs),
                'Status logs retrieved successfully'
            );
        } catch (\Exception $e) {
            return ApiResponse::error('Appointment not found', 404);
        }
    }
}

