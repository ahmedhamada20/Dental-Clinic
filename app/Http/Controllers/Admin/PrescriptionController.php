<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Models\Medical\Prescription;
use App\Models\Patient\Patient;
use App\Models\User;
use App\Models\Visit\Visit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PrescriptionController extends Controller
{
    /**
     * Display a listing of prescriptions.
     */
    public function index(Request $request)
    {
        $query = Prescription::query()
            ->with([
                'patient',
                'doctor',
                'visit',
            ])
            ->withCount('items');

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('notes', 'like', "%{$search}%")
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

        // Date filter
        if ($request->has('date') && $request->date) {
            $query->whereDate('issued_at', $request->date);
        }

        // Date range filter
        if ($request->has('from_date') && $request->from_date) {
            $query->whereDate('issued_at', '>=', $request->from_date);
        }
        if ($request->has('to_date') && $request->to_date) {
            $query->whereDate('issued_at', '<=', $request->to_date);
        }

        // Patient filter
        if ($request->has('patient_id') && $request->patient_id) {
            $query->where('patient_id', $request->patient_id);
        }

        // Doctor filter
        if ($request->has('doctor_id') && $request->doctor_id) {
            $query->where('doctor_id', $request->doctor_id);
        }

        $prescriptions = $query->latest('issued_at')->paginate(15);

        return view('admin.prescriptions.index', compact('prescriptions'));
    }

    public function create(Request $request)
    {
        $selectedPatientId = $request->integer('patient_id') ?: null;

        return view('admin.prescriptions.create', [
            'prescription' => new Prescription([
                'patient_id' => $selectedPatientId,
                'visit_id' => $request->integer('visit_id') ?: null,
                'doctor_id' => auth()->id(),
                'issued_at' => now(),
            ]),
            'patients' => Patient::query()->orderBy('full_name')->get(),
            'doctors' => User::query()->where('user_type', UserType::DOCTOR->value)->orderBy('full_name')->get(),
            'visits' => $selectedPatientId
                ? Visit::query()->where('patient_id', $selectedPatientId)->latest('visit_date')->get()
                : collect(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validatePrescription($request);

        $prescription = DB::transaction(function () use ($validated) {
            $prescription = Prescription::query()->create([
                'patient_id' => $validated['patient_id'],
                'visit_id' => $validated['visit_id'],
                'doctor_id' => $validated['doctor_id'],
                'notes' => $validated['notes'] ?? null,
                'issued_at' => $validated['issued_at'] ?? now(),
            ]);

            $prescription->items()->createMany($this->buildPrescriptionItemsPayload($validated['items']));

            return $prescription;
        });

        return redirect()
            ->route('admin.prescriptions.show', $prescription)
            ->with('success', __('admin.prescriptions.created'));
    }

    /**
     * Display the specified prescription.
     */
    public function show(Prescription $prescription)
    {
        $prescription->load([
            'patient',
            'doctor',
            'visit',
            'items',
        ]);

        return view('admin.prescriptions.show', compact('prescription'));
    }

    public function edit(Prescription $prescription)
    {
        $prescription->load(['items']);

        return view('admin.prescriptions.edit', [
            'prescription' => $prescription,
            'patients' => Patient::query()->orderBy('full_name')->get(),
            'doctors' => User::query()->where('user_type', UserType::DOCTOR->value)->orderBy('full_name')->get(),
            'visits' => Visit::query()->where('patient_id', $prescription->patient_id)->latest('visit_date')->get(),
        ]);
    }

    public function update(Request $request, Prescription $prescription)
    {
        $validated = $this->validatePrescription($request);

        DB::transaction(function () use ($prescription, $validated) {
            $prescription->update([
                'patient_id' => $validated['patient_id'],
                'visit_id' => $validated['visit_id'],
                'doctor_id' => $validated['doctor_id'],
                'notes' => $validated['notes'] ?? null,
                'issued_at' => $validated['issued_at'] ?? now(),
            ]);

            $prescription->items()->delete();
            $prescription->items()->createMany($this->buildPrescriptionItemsPayload($validated['items']));
        });

        return redirect()
            ->route('admin.prescriptions.show', $prescription)
            ->with('success', __('admin.prescriptions.updated'));
    }

    /**
     * Print the specified prescription.
     */
    public function print(Prescription $prescription)
    {
        $prescription->load([
            'patient',
            'doctor',
            'visit',
            'items',
        ]);

        return view('admin.prescriptions.print', compact('prescription'));
    }

    /**
     * Display a patient prescription from patient profile context.
     */
    public function showForPatient(Patient $patient, Prescription $prescription)
    {
        abort_unless($prescription->patient_id === $patient->id, 404);

        return $this->show($prescription);
    }

    /**
     * Print a patient prescription from patient profile context.
     */
    public function printForPatient(Patient $patient, Prescription $prescription)
    {
        abort_unless($prescription->patient_id === $patient->id, 404);

        return $this->print($prescription);
    }

    /**
     * Print all prescriptions for a patient.
     */
    public function printAllForPatient(Patient $patient)
    {
        $prescriptions = $patient->prescriptions()->with(['doctor', 'visit', 'items'])->latest('issued_at')->get();
        return view('admin.prescriptions.print-all', compact('patient', 'prescriptions'));
    }

    public function visitsByPatient(Patient $patient): JsonResponse
    {
        $visits = Visit::query()
            ->where('patient_id', $patient->id)
            ->latest('visit_date')
            ->get(['id', 'visit_no', 'visit_date']);

        return response()->json([
            'visits' => $visits->map(fn (Visit $visit) => [
                'id' => $visit->id,
                'visit_no' => $visit->visit_no,
                'visit_date' => optional($visit->visit_date)->format('Y-m-d'),
            ])->values(),
        ]);
    }

    private function validatePrescription(Request $request): array
    {
        $patientId = $request->integer('patient_id');

        return $request->validate([
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'visit_id' => [
                'required',
                'integer',
                Rule::exists('visits', 'id')->where(fn ($query) => $query->where('patient_id', $patientId)),
            ],
            'doctor_id' => ['required', 'integer', 'exists:users,id'],
            'issued_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.medicine_name' => ['required', 'string', 'max:255'],
            'items.*.dosage' => ['nullable', 'string', 'max:255'],
            'items.*.frequency' => ['nullable', 'string', 'max:255'],
            'items.*.dose_duration' => ['nullable', 'string', 'max:255'],
            'items.*.treatment_duration' => ['nullable', 'string', 'max:255'],
            'items.*.duration' => ['nullable', 'string', 'max:255'],
            'items.*.instructions' => ['nullable', 'string', 'max:1000'],
        ]);
    }

    private function buildPrescriptionItemsPayload(array $items): array
    {
        return collect($items)
            ->filter(fn (array $item) => filled($item['medicine_name'] ?? null))
            ->map(function (array $item) {
                $treatmentDuration = trim((string) ($item['treatment_duration'] ?? ''));

                return [
                    'medicine_name' => trim((string) $item['medicine_name']),
                    'dosage' => $item['dosage'] ?? null,
                    'frequency' => $item['frequency'] ?? null,
                    'dose_duration' => $item['dose_duration'] ?? null,
                    'treatment_duration' => $treatmentDuration !== '' ? $treatmentDuration : null,
                    'duration' => $treatmentDuration !== '' ? $treatmentDuration : ($item['duration'] ?? null),
                    'instructions' => $item['instructions'] ?? null,
                ];
            })
            ->values()
            ->all();
    }
}

