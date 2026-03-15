<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Patient\Patient;
use App\Policies\OdontogramPolicy;
use App\Models\Visit\Visit;
use Illuminate\Http\Request;

class OdontogramHistoryController extends Controller
{
    public function index(Request $request, Visit $visit)
    {
        abort_unless(app(OdontogramPolicy::class)->viewHistory($request->user()), 403);

        $visit->loadMissing(['patient', 'appointment']);
        $patient = $visit->patient
            ?? ($visit->patient_id ? Patient::query()->find($visit->patient_id) : null)
            ?? ($visit->appointment?->patient_id ? Patient::query()->find($visit->appointment->patient_id) : null);

        abort_if(! $patient, 404);

        $history = $patient->odontogramHistory()
            ->with(['changedBy', 'visit'])
            ->when($request->filled('tooth_number'), fn ($query) => $query->where('tooth_number', $request->string('tooth_number')->toString()))
            ->latest('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.visits.odontogram-history', [
            'visit' => $visit,
            'history' => $history,
            'selectedTooth' => $request->string('tooth_number')->toString(),
        ]);
    }
}
