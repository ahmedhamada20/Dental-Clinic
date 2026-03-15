<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Patient\Patient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PatientMedicalHistoryController extends Controller
{
    public function store(Request $request, Patient $patient): RedirectResponse
    {
        $validated = $request->validate([
            'allergies' => 'nullable|string',
            'chronic_diseases' => 'nullable|string',
            'current_medications' => 'nullable|string',
            'medical_notes' => 'nullable|string',
            'dental_history' => 'nullable|string',
            'important_alerts' => 'nullable|string',
        ]);

        $patient->medicalHistory()->updateOrCreate([], [
            ...$validated,
            'updated_by' => $request->user()?->id,
        ]);

        return redirect()
            ->route('admin.patients.show', ['patient' => $patient->id, 'tab' => 'history'])
            ->with('success', __('admin.messages.patients.medical_history_saved'));
    }
}

