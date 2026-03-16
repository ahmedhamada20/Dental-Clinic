<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AppointmentStatus;
use App\Enums\BookingSource;
use App\Enums\UserType;
use App\Enums\WaitingListStatus;
use App\Http\Controllers\Concerns\AppliesSpecialtyScope;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AppointmentCancelRequest;
use App\Http\Requests\Admin\AppointmentRescheduleRequest;
use App\Http\Requests\Admin\AppointmentStatusTransitionRequest;
use App\Models\Appointment\Appointment;
use App\Models\Appointment\WaitingListRequest;
use App\Models\Clinic\MedicalSpecialty;
use App\Models\Clinic\Service;
use App\Models\Patient\Patient;
use App\Models\User;
use App\Modules\Appointments\Rules\DoctorMatchesSpecialty;
use App\Modules\Appointments\Rules\ServiceMatchesSpecialty;
use App\Modules\Audit\Services\AuditLogService;
use App\Services\AppointmentWorkflowService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    use AppliesSpecialtyScope;

    public function __construct(
        private AppointmentWorkflowService $workflowService,
        private readonly AuditLogService $auditLogService,
    ) {
    }

    public function index(Request $request): View
    {
        $appointment = new Appointment();
        $table = $appointment->getTable();

        $query = $this->scopeAppointments(Appointment::query()->with(['patient', 'doctor.specialty', 'service.category.medicalSpecialty', 'specialty']));


        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());
            $query->where(function ($q) use ($search) {
                $q->whereHas('patient', function ($patientQuery) use ($search) {
                    $patientQuery->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('full_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('patient_code', 'like', "%{$search}%");
                });
            });
        }

        if ($request->filled('status') && Schema::hasColumn($table, 'status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('status') && Schema::hasColumn($table, 'status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('date') && Schema::hasColumn($table, 'appointment_date')) {
            $query->whereDate('appointment_date', $request->string('date'));
        }

        if ($request->filled('specialty_id') && Schema::hasColumn($table, 'specialty_id')) {
            $query->where('specialty_id', $request->integer('specialty_id'));
        }

        if (Schema::hasColumn($table, 'appointment_date')) {
            $query->latest('appointment_date')->latest('start_time');
        } else {
            $query->latest();
        }

        $appointments = $query->paginate(15)->withQueryString();

        $statuses = AppointmentStatus::cases();
        $specialties = MedicalSpecialty::query()->where('is_active', true)->orderBy('name')->get();

        return view('admin.appointments.index', compact('appointments', 'statuses', 'specialties'));
    }

    public function daily(Request $request): View
    {
        $date = Carbon::parse($request->input('date', now()->toDateString()))->toDateString();

        $appointments = $this->scopeAppointments(Appointment::query())
            ->with(['patient', 'doctor', 'service', 'specialty'])
            ->whereDate('appointment_date', $date)
            ->orderBy('start_time')
            ->paginate(25)
            ->withQueryString();

        return view('admin.appointments.daily', [
            'appointments' => $appointments,
            'date' => $date,
        ]);
    }

    public function calendar(Request $request): View
    {
        $monthInput = $request->input('month', now()->format('Y-m'));
        $monthDate = Carbon::createFromFormat('Y-m', $monthInput)->startOfMonth();

        $appointments = $this->scopeAppointments(Appointment::query())
            ->with(['patient', 'specialty'])
            ->whereBetween('appointment_date', [$monthDate->copy()->startOfMonth()->toDateString(), $monthDate->copy()->endOfMonth()->toDateString()])
            ->orderBy('appointment_date')
            ->orderBy('start_time')
            ->get()
            ->groupBy(fn (Appointment $a) => optional($a->appointment_date)->toDateString());

        return view('admin.appointments.calendar', [
            'calendarDate' => $monthDate,
            'appointmentsByDate' => $appointments,
        ]);
    }

    public function timeline(Request $request): View
    {
        $query = $this->scopeAppointments(Appointment::query()->with(['patient', 'doctor', 'service', 'specialty']));

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $appointments = $query
            ->orderByDesc('appointment_date')
            ->orderByDesc('start_time')
            ->paginate(30)
            ->withQueryString();

        return view('admin.appointments.timeline', [
            'appointments' => $appointments,
            'statuses' => AppointmentStatus::cases(),
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.appointments.create', $this->buildFormData($request));
    }

    public function formOptions(Request $request): JsonResponse
    {
        $specialtyId = $request->integer('specialty_id');

        if (! $this->isSpecialtyScopeBypassed() && $this->currentSpecialtyId() !== $specialtyId) {
            return response()->json([
                'doctors' => [],
                'services' => [],
            ]);
        }

        if (!$specialtyId) {
            return response()->json([
                'doctors' => [],
                'services' => [],
            ]);
        }

        return response()->json([
            'doctors' => $this->loadDoctors($specialtyId)->map(fn (User $doctor) => [
                'id' => $doctor->id,
                'name' => $doctor->display_name,
            ])->values(),
            'services' => $this->loadServices($specialtyId)->map(fn (Service $service) => [
                'id' => $service->id,
                'name' => $service->name_en ?: $service->name_ar,
            ])->values(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateAppointment($request);
        $this->assertAppointmentInputWithinScope($validated);
        [$startTime, $endTime] = $this->resolveTimes($validated['appointment_date'], $validated['appointment_time'], (int) $validated['service_id']);

        $appointment = Appointment::create([
            'appointment_no' => $this->generateAppointmentNo(),
            'patient_id' => $validated['patient_id'],
            'specialty_id' => $validated['specialty_id'],
            'service_id' => $validated['service_id'],
            'assigned_doctor_id' => $validated['doctor_id'],
            'appointment_date' => $validated['appointment_date'],
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => $validated['status'],
            'booking_source' => BookingSource::WEB_APP,
            'notes' => $validated['notes'] ?? null,
        ]);

        $this->auditLogService->log('appointments', 'create', $appointment, null, $appointment->toArray());

        return redirect()
            ->route('admin.appointments.show', $appointment)
            ->with('success', __('admin.appointments.created'));
    }

    public function confirm(Appointment $appointment, AppointmentStatusTransitionRequest $request): RedirectResponse
    {
        $this->ensureCanAccessAppointment($appointment);

        $this->workflowService->confirm($appointment, auth()->id(), $request->input('notes'));

        return redirect()->back()->with('success', __('admin.appointments.confirmed'));
    }

    public function checkIn(Appointment $appointment, AppointmentStatusTransitionRequest $request): RedirectResponse
    {
        $this->ensureCanAccessAppointment($appointment);

        $this->workflowService->checkIn($appointment, auth()->id(), $request->input('notes'));

        return redirect()->back()->with('success', __('admin.appointments.checked_in'));
    }

    public function markNoShow(Appointment $appointment, AppointmentStatusTransitionRequest $request): RedirectResponse
    {
        $this->ensureCanAccessAppointment($appointment);

        $this->workflowService->markNoShow($appointment, auth()->id(), $request->input('notes'));

        return redirect()->back()->with('success', __('admin.appointments.marked_no_show'));
    }

    public function complete(Appointment $appointment, AppointmentStatusTransitionRequest $request): RedirectResponse
    {
        $this->ensureCanAccessAppointment($appointment);

        $this->workflowService->complete($appointment, auth()->id(), $request->input('notes'));

        return redirect()->back()->with('success', __('admin.appointments.completed'));
    }

    public function cancel(Appointment $appointment, AppointmentCancelRequest $request): RedirectResponse
    {
        $this->ensureCanAccessAppointment($appointment);

        $reschedule = null;

        if ($request->filled('reschedule_date') && $request->filled('reschedule_time')) {
            $reschedule = [
                'date' => $request->input('reschedule_date'),
                'time' => $request->input('reschedule_time'),
            ];
        }

        $waitingListRequestId = $request->boolean('convert_waiting_list')
            ? $request->input('waiting_list_request_id')
            : null;

        $this->workflowService->cancel(
            $appointment,
            auth()->id(),
            $request->input('cancellation_reason'),
            $request->input('notes'),
            $waitingListRequestId,
            $reschedule
        );

        return redirect()->back()->with('success', __('admin.appointments.cancelled'));
    }

    public function reschedule(Appointment $appointment, AppointmentRescheduleRequest $request): RedirectResponse
    {
        $this->ensureCanAccessAppointment($appointment);

        $this->workflowService->reschedule(
            $appointment,
            auth()->id(),
            $request->input('appointment_date'),
            $request->input('appointment_time'),
            $request->input('notes')
        );

        return redirect()->back()->with('success', __('admin.appointments.rescheduled'));
    }

    public function show(Appointment $appointment): View
    {
        $this->ensureCanAccessAppointment($appointment);

        $appointment->loadMissing(['patient', 'doctor.specialty', 'service.category.medicalSpecialty', 'specialty']);
        $waitingListCandidates = WaitingListRequest::query()
            ->where('status', WaitingListStatus::PENDING->value)
            ->when(! $this->isSpecialtyScopeBypassed() && $this->currentSpecialtyId(), function ($query) {
                $specialtyId = $this->currentSpecialtyId();
                $query->whereHas('patient.appointments', fn ($appointmentQuery) => $appointmentQuery->where('specialty_id', $specialtyId));
            })
            ->with(['patient', 'service'])
            ->orderBy('preferred_date')
            ->get();

        return view('admin.appointments.show', compact('appointment', 'waitingListCandidates'));
    }

    public function edit(Request $request, Appointment $appointment): View
    {
        $this->ensureCanAccessAppointment($appointment);

        return view('admin.appointments.edit', $this->buildFormData($request, $appointment));
    }

    public function update(Request $request, Appointment $appointment): RedirectResponse
    {
        $this->ensureCanAccessAppointment($appointment);

        $validated = $this->validateAppointment($request, $appointment);
        $this->assertAppointmentInputWithinScope($validated);
        [$startTime, $endTime] = $this->resolveTimes($validated['appointment_date'], $validated['appointment_time'], (int) $validated['service_id']);

        $before = $appointment->getOriginal();
        $appointment->update([
            'patient_id' => $validated['patient_id'],
            'specialty_id' => $validated['specialty_id'],
            'service_id' => $validated['service_id'],
            'assigned_doctor_id' => $validated['doctor_id'],
            'appointment_date' => $validated['appointment_date'],
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
        ]);

        $action = ($before['status'] ?? null) !== $appointment->getRawOriginal('status')
            ? 'status_change'
            : 'update';

        $this->auditLogService->log('appointments', $action, $appointment, $before, $appointment->fresh()?->toArray() ?? $appointment->toArray());

        return redirect()
            ->route('admin.appointments.show', $appointment)
            ->with('success', __('admin.appointments.updated'));
    }

    public function destroy(Appointment $appointment): RedirectResponse
    {
        $this->ensureCanAccessAppointment($appointment);

        $before = $appointment->toArray();
        $appointment->delete();

        $this->auditLogService->log('appointments', 'delete', Appointment::class, $before, null);

        return redirect()
            ->route('admin.appointments.index')
            ->with('success', __('admin.appointments.deleted'));
    }

    protected function buildFormData(Request $request, ?Appointment $appointment = null): array
    {
        $appointment?->loadMissing(['patient', 'doctor', 'service.category', 'specialty']);

        $selectedSpecialtyId = (int) ($request->input('specialty_id')
            ?? old('specialty_id')
            ?? $appointment?->specialty_id);

        if (! $this->isSpecialtyScopeBypassed() && $this->currentSpecialtyId()) {
            $selectedSpecialtyId = (int) $this->currentSpecialtyId();
        }

        $patients = $this->scopePatients(Patient::query())->orderBy('full_name')->get();
        $specialties = MedicalSpecialty::query()
            ->where('is_active', true)
            ->when(! $this->isSpecialtyScopeBypassed() && $this->currentSpecialtyId(), fn ($query) => $query->whereKey($this->currentSpecialtyId()))
            ->orderBy('name')
            ->get();
        $doctors = $this->loadDoctors($selectedSpecialtyId ?: null);
        $services = $this->loadServices($selectedSpecialtyId ?: null);
        $statuses = AppointmentStatus::cases();

        return compact('appointment', 'patients', 'specialties', 'doctors', 'services', 'statuses', 'selectedSpecialtyId');
    }

    protected function validateAppointment(Request $request, ?Appointment $appointment = null): array
    {
        $specialtyId = $request->integer('specialty_id');

        return $request->validate([
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'specialty_id' => ['required', 'integer', 'exists:medical_specialties,id'],
            'doctor_id' => ['required', 'integer', 'exists:users,id', new DoctorMatchesSpecialty($specialtyId)],
            'service_id' => ['required', 'integer', 'exists:services,id', new ServiceMatchesSpecialty($specialtyId)],
            'appointment_date' => ['required', 'date', 'date_format:Y-m-d', Rule::when($appointment === null, ['after_or_equal:today'])],
            'appointment_time' => ['required', 'date_format:H:i'],
            'status' => ['required', Rule::in(array_map(fn (AppointmentStatus $status) => $status->value, AppointmentStatus::cases()))],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);
    }

    protected function resolveTimes(string $appointmentDate, string $appointmentTime, int $serviceId): array
    {
        $service = Service::query()->findOrFail($serviceId);
        $duration = $service->duration_minutes ?? 30;
        $startDateTime = Carbon::createFromFormat('Y-m-d H:i', "{$appointmentDate} {$appointmentTime}");

        return [$startDateTime->format('H:i'), $startDateTime->copy()->addMinutes($duration)->format('H:i')];
    }

    protected function loadDoctors(?int $specialtyId = null)
    {
        $effectiveSpecialtyId = $this->isSpecialtyScopeBypassed()
            ? $specialtyId
            : $this->currentSpecialtyId();

        return User::query()
            ->where('user_type', UserType::DOCTOR->value)
            ->when($effectiveSpecialtyId, fn ($query) => $query->where('specialty_id', $effectiveSpecialtyId))
            ->with('specialty')
            ->orderBy('full_name')
            ->get();
    }

    protected function loadServices(?int $specialtyId = null)
    {
        $effectiveSpecialtyId = $this->isSpecialtyScopeBypassed()
            ? $specialtyId
            : $this->currentSpecialtyId();

        return Service::query()
            ->with(['category.medicalSpecialty'])
            ->where('is_active', true)
            ->where('is_bookable', true)
            ->when($effectiveSpecialtyId, function ($query) use ($effectiveSpecialtyId) {
                $query->whereHas('category', fn ($categoryQuery) => $categoryQuery->where('medical_specialty_id', $effectiveSpecialtyId));
            })
            ->orderBy('name_en')
            ->orderBy('name_ar')
            ->get();
    }

    protected function generateAppointmentNo(): string
    {
        return 'APT-' . now()->format('YmdHis') . '-' . random_int(1000, 9999);
    }

    private function assertAppointmentInputWithinScope(array $validated): void
    {
        if ($this->isSpecialtyScopeBypassed()) {
            return;
        }

        $specialtyId = $this->currentSpecialtyId();
        abort_unless($specialtyId && (int) $validated['specialty_id'] === $specialtyId, 422, 'Selected specialty is outside your access scope.');

        $patient = Patient::query()->findOrFail((int) $validated['patient_id']);
        $this->ensureCanAccessPatient($patient);

        $doctor = User::query()->findOrFail((int) $validated['doctor_id']);
        $this->ensureUserBelongsToCurrentSpecialty($doctor);
    }
}
