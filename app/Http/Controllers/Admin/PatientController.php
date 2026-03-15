<?php

namespace App\Http\Controllers\Admin;

use App\Enums\FileCategory;
use App\Enums\AppointmentStatus;
use App\Enums\PatientStatus;
use App\Http\Controllers\Concerns\AppliesSpecialtyScope;
use App\Http\Controllers\Controller;
use App\Models\Patient\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PatientController extends Controller
{
    use AppliesSpecialtyScope;

    private const DASHBOARD_FILE_CATEGORIES = [
        'xray' => 'X-Ray',
        'prescription' => 'Prescription',
        'treatment_document' => 'Treatment Document',
        'before_after' => 'Before / After',
        'lab_result' => 'Lab Result',
        'other' => 'Other',
    ];

    /**
     * Display a listing of patients.
     */
    public function index(Request $request)
    {
        $query = $this->scopePatients(Patient::query()->with(['appointments', 'invoices', 'emergencyContacts', 'medicalFiles']));

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('full_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('patient_code', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $patients = $query->latest()->paginate(15)->withQueryString();
        $summaryBase = $this->scopePatients(Patient::query());
        $summary = [
            'total' => (clone $summaryBase)->count(),
            'active' => (clone $summaryBase)->where('status', PatientStatus::ACTIVE)->count(),
            'inactive' => (clone $summaryBase)->where('status', PatientStatus::INACTIVE)->count(),
            'withAlerts' => (clone $summaryBase)->whereHas('medicalHistory', fn ($history) => $history->whereNotNull('important_alerts')->where('important_alerts', '!=', ''))->count(),
        ];
        $statuses = PatientStatus::cases();

        return view('admin.patients.index', compact('patients', 'summary', 'statuses'));
    }

    /**
     * Show the form for creating a new patient.
     */
    public function create()
    {
        $statuses = PatientStatus::cases();
        $fileCategories = self::DASHBOARD_FILE_CATEGORIES;

        return view('admin.patients.create', [
            'statuses' => $statuses,
            'fileCategories' => $fileCategories,
            'patient' => new Patient(),
        ]);
    }

    /**
     * Store a newly created patient in storage.
     */
    public function store(Request $request)
    {
        $validated = $this->validatePatientRequest($request);
        $patient = DB::transaction(fn () => $this->persistPatientRecord(new Patient(), $validated, $request));

        return redirect()
            ->route('admin.patients.show', $patient)
            ->with('success', __('admin.messages.patients.created'));
    }

    /**
     * Display the specified patient.
     */
    public function show(Patient $patient)
    {
        $this->ensureCanAccessPatient($patient);
        $specialtyId = $this->currentSpecialtyId();
        $restrictBySpecialty = ! $this->isSpecialtyScopeBypassed() && $specialtyId;

        $patient->load([
            'profile',
            'medicalHistory.updatedBy',
            'emergencyContacts',
            'appointments' => fn ($query) => ($restrictBySpecialty ? $query->where('specialty_id', $specialtyId) : $query)->latest('appointment_date')->limit(10),
            'visits' => fn ($query) => ($restrictBySpecialty ? $query->whereHas('doctor', fn ($doctor) => $doctor->where('specialty_id', $specialtyId)) : $query)->with(['doctor', 'appointment'])->latest('visit_date')->limit(10),
            'prescriptions' => fn ($query) => ($restrictBySpecialty ? $query->whereHas('doctor', fn ($doctor) => $doctor->where('specialty_id', $specialtyId)) : $query)->with(['doctor', 'visit'])->latest('issued_at')->limit(10),
            'invoices' => fn ($query) => ($restrictBySpecialty ? $query->whereHas('visit.doctor', fn ($doctor) => $doctor->where('specialty_id', $specialtyId)) : $query)->with(['createdBy', 'visit'])->latest('issued_at')->limit(10),
            'medicalFiles' => fn ($query) => ($restrictBySpecialty ? $query->whereHas('visit.doctor', fn ($doctor) => $doctor->where('specialty_id', $specialtyId)) : $query)->with(['uploadedBy', 'visit'])->latest('uploaded_at')->limit(10),
        ]);

        $completedAppointments = ($restrictBySpecialty ? $patient->appointments()->where('specialty_id', $specialtyId) : $patient->appointments())
            ->with(['doctor', 'service', 'visit'])
            ->where('status', AppointmentStatus::COMPLETED)
            ->latest('appointment_date')
            ->paginate(10, ['*'], 'appointments_page')
            ->withQueryString();

        $visitHistory = ($restrictBySpecialty ? $patient->visits()->whereHas('doctor', fn ($doctor) => $doctor->where('specialty_id', $specialtyId)) : $patient->visits())
            ->with(['doctor', 'appointment'])
            ->latest('visit_date')
            ->paginate(10, ['*'], 'visits_page')
            ->withQueryString();

        $patientPrescriptions = ($restrictBySpecialty ? $patient->prescriptions()->whereHas('doctor', fn ($doctor) => $doctor->where('specialty_id', $specialtyId)) : $patient->prescriptions())
            ->with(['doctor', 'visit'])
            ->withCount('items')
            ->latest('issued_at')
            ->paginate(10, ['*'], 'prescriptions_page')
            ->withQueryString();

        $allAppointments = ($restrictBySpecialty ? $patient->appointments()->where('specialty_id', $specialtyId) : $patient->appointments())
            ->with(['doctor', 'service', 'specialty'])
            ->latest('appointment_date')
            ->paginate(10, ['*'], 'all_appointments_page')
            ->withQueryString();

        $timeline = collect()
            ->merge($patient->appointments->map(fn ($appointment) => [
                'type' => 'appointment',
                'date' => $appointment->appointment_date,
                'title' => __('admin.timeline.appointment', ['number' => $appointment->appointment_no ?? ('#' . $appointment->id)]),
                'subtitle' => $appointment->status?->label() ?? ucfirst((string) $appointment->status?->value),
                'description' => $appointment->reason ?? $appointment->notes,
                'route' => route('admin.appointments.show', $appointment),
            ]))
            ->merge($patient->visits->map(fn ($visit) => [
                'type' => 'visit',
                'date' => $visit->visit_date,
                'title' => __('admin.timeline.visit', ['number' => $visit->visit_no]),
                'subtitle' => $visit->status?->label() ?? ucfirst((string) $visit->status?->value),
                'description' => $visit->chief_complaint ?: $visit->diagnosis,
                'route' => route('admin.visits.show', $visit),
            ]))
            ->merge($patient->prescriptions->map(fn ($prescription) => [
                'type' => 'prescription',
                'date' => $prescription->issued_at,
                'title' => __('admin.timeline.prescription', ['number' => '#' . $prescription->id]),
                'subtitle' => $prescription->doctor?->displayName ?? __('admin.common.not_assigned'),
                'description' => $prescription->notes,
                'route' => route('admin.prescriptions.show', $prescription),
            ]))
            ->merge($patient->invoices->map(fn ($invoice) => [
                'type' => 'invoice',
                'date' => $invoice->issued_at,
                'title' => __('admin.timeline.invoice', ['number' => $invoice->invoice_no]),
                'subtitle' => $invoice->status?->label() ?? ucfirst((string) $invoice->status?->value),
                'description' => $invoice->notes,
                'route' => route('admin.billing.invoices.index', ['search' => $invoice->invoice_no]),
            ]))
            ->merge($patient->medicalFiles->map(fn ($file) => [
                'type' => 'file',
                'date' => $file->uploaded_at,
                'title' => $file->title,
                'subtitle' => $this->formatMedicalFileCategory($file->file_category?->value ?? $file->file_category),
                'description' => $file->notes,
                'route' => route('admin.patients.show', ['patient' => $patient->id, 'tab' => 'files']),
            ]))
            ->filter(fn ($item) => filled($item['date']))
            ->sortByDesc('date')
            ->values();

        $stats = [
            'appointments' => $patient->appointments()->count(),
            'visits' => $patient->visits()->count(),
            'prescriptions' => $patient->prescriptions()->count(),
            'invoices' => $patient->invoices()->count(),
            'files' => $patient->medicalFiles()->count(),
        ];

        return view('admin.patients.show', [
            'patient' => $patient,
            'timeline' => $timeline,
            'stats' => $stats,
            'completedAppointments' => $completedAppointments,
            'visitHistory' => $visitHistory,
            'patientPrescriptions' => $patientPrescriptions,
            'allAppointments' => $allAppointments,
            'tab' => request('tab', 'overview'),
        ]);
    }

    /**
     * Show the form for editing the specified patient.
     */
    public function edit(Patient $patient)
    {
        $this->ensureCanAccessPatient($patient);

        $patient->load(['profile', 'medicalHistory', 'emergencyContacts', 'medicalFiles']);
        $statuses = PatientStatus::cases();
        $fileCategories = self::DASHBOARD_FILE_CATEGORIES;

        return view('admin.patients.edit', compact('patient', 'statuses', 'fileCategories'));
    }

    /**
     * Update the specified patient in storage.
     */
    public function update(Request $request, Patient $patient)
    {
        $this->ensureCanAccessPatient($patient);

        $validated = $this->validatePatientRequest($request, $patient);
        $patient = DB::transaction(fn () => $this->persistPatientRecord($patient, $validated, $request));

        return redirect()
            ->route('admin.patients.show', $patient)
            ->with('success', __('admin.messages.patients.updated'));
    }

    /**
     * Remove the specified patient from storage.
     */
    public function destroy(Patient $patient)
    {
        $this->ensureCanAccessPatient($patient);

        try {
            $patient->delete();

            return redirect()
                ->route('admin.patients.index')
                ->with('success', __('admin.messages.patients.deleted'));
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.patients.index')
                ->with('error', __('admin.messages.patients.delete_failed'));
        }
    }

    private function validatePatientRequest(Request $request, ?Patient $patient = null): array
    {
        $patientId = $patient?->id;

        return $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => [
                'required',
                'string',
                'max:20',
                Rule::unique('patients', 'phone')->ignore($patientId),
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('patients', 'email')->ignore($patientId),
            ],
            'gender' => 'required|in:male,female,other',
            'date_of_birth' => 'required|date|before:today',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'alternate_phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
            'status' => ['required', Rule::enum(PatientStatus::class)],
            'password' => [$patient ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'profile.occupation' => 'nullable|string|max:255',
            'profile.marital_status' => 'nullable|string|max:100',
            'profile.preferred_language' => 'nullable|string|max:100',
            'profile.blood_group' => 'nullable|string|max:20',
            'profile.notes' => 'nullable|string',
            'medical_history.allergies' => 'nullable|string',
            'medical_history.chronic_diseases' => 'nullable|string',
            'medical_history.current_medications' => 'nullable|string',
            'medical_history.medical_notes' => 'nullable|string',
            'medical_history.dental_history' => 'nullable|string',
            'medical_history.important_alerts' => 'nullable|string',
            'emergency_contacts' => 'nullable|array',
            'emergency_contacts.*.name' => 'nullable|string|max:255',
            'emergency_contacts.*.relation' => 'nullable|string|max:100',
            'emergency_contacts.*.phone' => 'nullable|string|max:20',
            'emergency_contacts.*.notes' => 'nullable|string',
            'new_file' => 'nullable|file|max:10240',
            'new_file_title' => 'nullable|string|max:255|required_with:new_file',
            'new_file_category' => ['nullable', 'string', Rule::in(array_keys(self::DASHBOARD_FILE_CATEGORIES))],
            'new_file_notes' => 'nullable|string',
            'new_file_visible_to_patient' => 'nullable|boolean',
        ]);
    }

    private function persistPatientRecord(Patient $patient, array $validated, Request $request): Patient
    {
        $patientData = collect($validated)->except(['profile', 'medical_history', 'emergency_contacts', 'new_file', 'new_file_title', 'new_file_category', 'new_file_notes', 'new_file_visible_to_patient'])->all();
        $patientData['patient_code'] = $patientData['patient_code'] ?? $patient->patient_code ?? ('PAT-' . strtoupper(Str::random(8)));
        $patientData['age'] = now()->diffInYears($validated['date_of_birth']);
        $patientData['full_name'] = trim($validated['first_name'] . ' ' . $validated['last_name']);
        $patientData['registered_from'] = $patient->exists ? $patient->registered_from : 'dashboard';

        if (!empty($validated['password'])) {
            $patientData['password'] = Hash::make($validated['password']);
        } else {
            unset($patientData['password']);
        }

        $patient->fill($patientData);
        $patient->save();

        $profileData = collect($validated['profile'] ?? [])->filter(fn ($value) => $value !== null && $value !== '')->all();
        if ($profileData !== []) {
            $patient->profile()->updateOrCreate([], $profileData);
        }

        $historyData = collect($validated['medical_history'] ?? [])->filter(fn ($value) => $value !== null && $value !== '')->all();
        if ($historyData !== []) {
            $historyData['updated_by'] = Auth::id();
            $patient->medicalHistory()->updateOrCreate([], $historyData);
        }

        $contacts = collect($validated['emergency_contacts'] ?? [])
            ->map(fn ($contact) => [
                'name' => trim((string) ($contact['name'] ?? '')),
                'relation' => $contact['relation'] ?? null,
                'phone' => trim((string) ($contact['phone'] ?? '')),
                'notes' => $contact['notes'] ?? null,
            ])
            ->filter(fn ($contact) => $contact['name'] !== '' || $contact['phone'] !== '')
            ->values();

        $patient->emergencyContacts()->delete();
        if ($contacts->isNotEmpty()) {
            $patient->emergencyContacts()->createMany($contacts->all());
        }

        if ($request->hasFile('new_file')) {
            $uploadedFile = $request->file('new_file');
            $storedPath = $uploadedFile->store("medical-files/patients/{$patient->id}", 'public');
            $patient->medicalFiles()->create([
                'uploaded_by' => Auth::id(),
                'file_category' => $validated['new_file_category'] ?? 'other',
                'title' => $validated['new_file_title'],
                'notes' => $validated['new_file_notes'] ?? null,
                'file_path' => $storedPath,
                'file_name' => $uploadedFile->getClientOriginalName(),
                'file_extension' => $uploadedFile->getClientOriginalExtension(),
                'mime_type' => $uploadedFile->getClientMimeType(),
                'file_size' => $uploadedFile->getSize(),
                'is_visible_to_patient' => (bool) ($validated['new_file_visible_to_patient'] ?? true),
                'uploaded_at' => now(),
            ]);
        }

        return $patient->fresh([
            'profile',
            'medicalHistory',
            'emergencyContacts',
            'medicalFiles',
        ]);
    }

    private function formatMedicalFileCategory(?string $value): string
    {
        if ($value === null || $value === '') {
            return 'File';
        }

        return self::DASHBOARD_FILE_CATEGORIES[$value]
            ?? FileCategory::tryFrom($value)?->label()
            ?? str($value)->replace('_', ' ')->title()->toString();
    }
}
