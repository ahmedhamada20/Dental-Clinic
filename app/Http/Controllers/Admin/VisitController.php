<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserType;
use App\Enums\VisitStatus;
use App\Http\Controllers\Concerns\AppliesSpecialtyScope;
use App\Http\Controllers\Controller;
use App\Models\Appointment\Appointment;
use App\Models\Appointment\VisitTicket;
use App\Models\Patient\Patient;
use App\Models\User;
use App\Models\Visit\Visit;
use App\Services\ReceptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class VisitController extends Controller
{
    use AppliesSpecialtyScope;

    protected ReceptionService $receptionService;

    public function __construct(ReceptionService $receptionService)
    {
        $this->receptionService = $receptionService;
    }

    /**
     * Display a listing of visits.
     */
    public function index(Request $request)
    {
        $query = $this->scopeVisits(Visit::query())
            ->with([
                'patient',
                'doctor',
                'appointment',
            ]);

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('visit_no', 'like', "%{$search}%")
                    ->orWhere('diagnosis', 'like', "%{$search}%")
                    ->orWhere('chief_complaint', 'like', "%{$search}%")
                    ->orWhereHas('patient', function ($pq) use ($search) {
                        $pq->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('full_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('doctor', function ($dq) use ($search) {
                        $dq->where('full_name', 'like', "%{$search}%")
                            ->orWhere('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
            });
        }

        // Status filter
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Date filter
        if ($request->has('date') && $request->date) {
            $query->whereDate('visit_date', $request->date);
        }

        // Date range filter
        if ($request->has('from_date') && $request->from_date) {
            $query->whereDate('visit_date', '>=', $request->from_date);
        }
        if ($request->has('to_date') && $request->to_date) {
            $query->whereDate('visit_date', '<=', $request->to_date);
        }

        $visits = $query->latest('visit_date')->paginate(15);

        return view('admin.visits.index', compact('visits'));
    }

    public function create(): View
    {
        return view('admin.visits.create', [
            'patients' => $this->scopePatients(Patient::query())->orderBy('full_name')->get(),
            'doctors' => $this->scopeUsersBySpecialty(User::query()->where('user_type', UserType::DOCTOR->value)->whereNotNull('specialty_id'))->orderBy('full_name')->get(),
            'appointments' => $this->loadAppointmentsForVisitForm(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateVisit($request);
        $this->assertVisitInputWithinScope($validated);
        unset($validated['visit_no']);

        $visit = DB::transaction(fn () => Visit::query()->create($validated));

        return redirect()
            ->route('admin.visits.show', $visit)
            ->with('success', __('admin.messages.visits.created'));
    }

    /**
     * Display the specified visit.
     */
    public function show(Visit $visit)
    {
        $this->ensureCanAccessVisit($visit);

        $visit->load([
            'patient',
            'doctor',
            'appointment',
            'checkedInBy',
            'ticket',
            'notes' => fn ($query) => $query
                ->with(['doctor', 'createdBy', 'updatedBy'])
                ->latest(),
            'treatmentPlans.items',
            'prescriptions.items',
            'invoice.items',
            'medicalFiles',
        ]);

        return view('admin.visits.show', [
            'visit' => $visit,
        ]);
    }

    public function edit(Visit $visit): View
    {
        $this->ensureCanAccessVisit($visit);

        return view('admin.visits.edit', [
            'visit' => $visit,
            'patients' => $this->scopePatients(Patient::query())->orderBy('full_name')->get(),
            'doctors' => $this->scopeUsersBySpecialty(User::query()->where('user_type', UserType::DOCTOR->value))->orderBy('full_name')->get(),
            'appointments' => $this->loadAppointmentsForVisitForm(),
        ]);
    }

    public function update(Request $request, Visit $visit): RedirectResponse
    {
        $this->ensureCanAccessVisit($visit);

        $validated = $this->validateVisit($request, $visit);
        $this->assertVisitInputWithinScope($validated);
        $visit->update($validated);

        return redirect()
            ->route('admin.visits.show', $visit)
            ->with('success', __('admin.messages.visits.updated'));
    }

    public function destroy(Visit $visit): RedirectResponse
    {
        $this->ensureCanAccessVisit($visit);

        $visit->delete();

        return redirect()
            ->route('admin.visits.index')
            ->with('success', __('admin.messages.visits.deleted'));
    }

    /**
     * Display today's queue page.
     */
    public function queue(Request $request)
    {
        $queue = $this->receptionService->getTodaysQueue();
        $activeVisits = $this->receptionService->getActiveVisits();

        if (! $this->isSpecialtyScopeBypassed() && $this->currentSpecialtyId()) {
            $specialtyId = $this->currentSpecialtyId();
            $queue = $queue->filter(fn ($ticket) => (int) optional(optional($ticket->visit)->doctor)->specialty_id === $specialtyId)->values();
            $activeVisits = $activeVisits->filter(fn ($visit) => (int) optional($visit->doctor)->specialty_id === $specialtyId)->values();
        }

        return view('admin.visits.queue', compact('queue', 'activeVisits'));
    }

    /**
     * Display active visits page.
     */
    public function activeVisits(Request $request)
    {
        $activeVisits = $this->receptionService->getActiveVisits();

        if (! $this->isSpecialtyScopeBypassed() && $this->currentSpecialtyId()) {
            $specialtyId = $this->currentSpecialtyId();
            $activeVisits = $activeVisits->filter(fn ($visit) => (int) optional($visit->doctor)->specialty_id === $specialtyId)->values();
        }

        return view('admin.visits.active', compact('activeVisits'));
    }

    /**
     * Display completed visits page.
     */
    public function completedVisits(Request $request)
    {
        $completedVisits = $this->receptionService->getCompletedVisitsToday();

        if (! $this->isSpecialtyScopeBypassed() && $this->currentSpecialtyId()) {
            $specialtyId = $this->currentSpecialtyId();
            $completedVisits = $completedVisits->filter(fn ($visit) => (int) optional($visit->doctor)->specialty_id === $specialtyId)->values();
        }

        return view('admin.visits.completed', compact('completedVisits'));
    }

    /**
     * Check in patient from appointment.
     */
    public function checkIn(Appointment $appointment): RedirectResponse
    {
        $result = $this->receptionService->checkInPatient($appointment, auth()->id());

        if ($result['success']) {
            return redirect()->route('admin.visits.queue')
                ->with('success', $result['message']);
        }

        return redirect()->back()
            ->with('error', $result['message']);
    }

    /**
     * Call patient from queue.
     */
    public function callFromQueue(VisitTicket $ticket): RedirectResponse
    {
        $result = $this->receptionService->callPatientFromQueue($ticket);

        if ($result['success']) {
            return redirect()->back()
                ->with('success', $result['message']);
        }

        return redirect()->back()
            ->with('error', $result['message']);
    }

    /**
     * Start a visit.
     */
    public function start(Visit $visit): RedirectResponse
    {
        $this->ensureCanAccessVisit($visit);

        $result = $this->receptionService->startVisit($visit);

        if ($result['success']) {
            return redirect()->route('admin.visits.show', $visit)
                ->with('success', $result['message']);
        }

        return redirect()->back()
            ->with('error', $result['message']);
    }

    /**
     * Complete a visit.
     */
    public function complete(Visit $visit, Request $request): RedirectResponse
    {
        $this->ensureCanAccessVisit($visit);

        $data = $request->only([
            'chief_complaint',
            'diagnosis',
            'clinical_notes',
            'internal_notes',
        ]);

        $result = $this->receptionService->completeVisit($visit, $data);

        if ($result['success']) {
            return redirect()->route('admin.visits.index')
                ->with('success', $result['message']);
        }

        return redirect()->back()
            ->with('error', $result['message']);
    }

    /**
     * Cancel a visit.
     */
    public function cancel(Visit $visit, Request $request): RedirectResponse
    {
        $this->ensureCanAccessVisit($visit);

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ], [
            'reason.required' => __('admin.validation.cancel_reason_required'),
        ]);

        $result = $this->receptionService->cancelVisit($visit, $validated['reason']);

        if ($result['success']) {
            return redirect()->route('admin.visits.queue')
                ->with('success', $result['message']);
        }

        return redirect()->back()
            ->with('error', $result['message']);
    }

    protected function validateVisit(Request $request, ?Visit $visit = null): array
    {
        $patientId = $request->integer('patient_id');
        $doctorId = $request->integer('doctor_id');

        return $request->validate([
            'visit_no' => [
                Rule::requiredIf($visit !== null),
                'nullable',
                'string',
                'max:255',
                Rule::unique('visits', 'visit_no')
                    ->where(fn ($query) => $query->where('doctor_id', $doctorId))
                    ->ignore($visit?->id),
            ],
            'appointment_id' => [
                'nullable',
                'integer',
                Rule::exists('appointments', 'id')->where(fn ($query) => $query->where('patient_id', $patientId)),
            ],
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'doctor_id' => ['required', 'integer', 'exists:users,id'],
            'visit_date' => ['required', 'date'],
            'start_at' => ['nullable', 'date'],
            'end_at' => ['nullable', 'date', 'after_or_equal:start_at'],
            'status' => ['required', 'in:' . implode(',', array_map(fn (VisitStatus $status) => $status->value, VisitStatus::cases()))],
            'chief_complaint' => ['nullable', 'string'],
            'diagnosis' => ['nullable', 'string'],
            'clinical_notes' => ['nullable', 'string'],
            'internal_notes' => ['nullable', 'string'],
        ]);
    }

    private function loadAppointmentsForVisitForm()
    {
        return $this->scopeAppointments(Appointment::query())
            ->select(['id', 'patient_id', 'appointment_no', 'appointment_date'])
            ->orderByDesc('appointment_date')
            ->orderByDesc('id')
            ->get();
    }

    private function assertVisitInputWithinScope(array $validated): void
    {
        if ($this->isSpecialtyScopeBypassed()) {
            return;
        }

        $patient = Patient::query()->findOrFail((int) $validated['patient_id']);
        $this->ensureCanAccessPatient($patient);

        $doctor = User::query()->findOrFail((int) $validated['doctor_id']);
        $this->ensureUserBelongsToCurrentSpecialty($doctor);

        if (! empty($validated['appointment_id'])) {
            $appointment = Appointment::query()->findOrFail((int) $validated['appointment_id']);
            $this->ensureCanAccessAppointment($appointment);
        }
    }
}

