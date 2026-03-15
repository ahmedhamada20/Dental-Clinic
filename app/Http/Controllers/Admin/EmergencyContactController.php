<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Patient\Patient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmergencyContactController extends Controller
{
    public function store(Request $request, Patient $patient): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'relation' => 'nullable|string|max:100',
            'phone' => 'required|string|max:20',
            'notes' => 'nullable|string',
        ]);

        $patient->emergencyContacts()->create($validated);

        return redirect()
            ->route('admin.patients.show', ['patient' => $patient->id, 'tab' => 'contacts'])
            ->with('success', __('admin.patients.emergency_contact_added'));
    }

    public function update(Request $request, Patient $patient, int $contact): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'relation' => 'nullable|string|max:100',
            'phone' => 'required|string|max:20',
            'notes' => 'nullable|string',
        ]);

        $patient->emergencyContacts()->whereKey($contact)->firstOrFail()->update($validated);

        return redirect()
            ->route('admin.patients.show', ['patient' => $patient->id, 'tab' => 'contacts'])
            ->with('success', __('admin.patients.emergency_contact_updated'));
    }

    public function destroy(Patient $patient, int $contact): RedirectResponse
    {
        $patient->emergencyContacts()->whereKey($contact)->firstOrFail()->delete();

        return redirect()
            ->route('admin.patients.show', ['patient' => $patient->id, 'tab' => 'contacts'])
            ->with('success', __('admin.patients.emergency_contact_removed'));
    }
}

