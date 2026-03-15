<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Patient\Patient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class MedicalFileController extends Controller
{
    private const DASHBOARD_FILE_CATEGORIES = [
        'xray',
        'prescription',
        'treatment_document',
        'before_after',
        'lab_result',
        'other',
    ];

    public function store(Request $request, Patient $patient): RedirectResponse
    {
        $validated = $request->validate([
            'file' => 'required|file|max:10240',
            'title' => 'required|string|max:255',
            'file_category' => ['required', 'string', Rule::in(self::DASHBOARD_FILE_CATEGORIES)],
            'notes' => 'nullable|string',
            'is_visible_to_patient' => 'nullable|boolean',
        ]);

        $uploadedFile = $request->file('file');
        $storedPath = $uploadedFile->store("medical-files/patients/{$patient->id}", 'public');

        $patient->medicalFiles()->create([
            'uploaded_by' => $request->user()?->id,
            'file_category' => $validated['file_category'],
            'title' => $validated['title'],
            'notes' => $validated['notes'] ?? null,
            'file_path' => $storedPath,
            'file_name' => $uploadedFile->getClientOriginalName(),
            'file_extension' => $uploadedFile->getClientOriginalExtension(),
            'mime_type' => $uploadedFile->getClientMimeType(),
            'file_size' => $uploadedFile->getSize(),
            'is_visible_to_patient' => (bool) ($validated['is_visible_to_patient'] ?? true),
            'uploaded_at' => now(),
        ]);

        return redirect()
            ->route('admin.patients.show', ['patient' => $patient->id, 'tab' => 'files'])
            ->with('success', __('admin.patients.medical_file_uploaded'));
    }

    public function destroy(Patient $patient, int $file): RedirectResponse
    {
        $medicalFile = $patient->medicalFiles()->whereKey($file)->firstOrFail();

        if ($medicalFile->file_path) {
            Storage::disk('public')->delete($medicalFile->file_path);
        }

        $medicalFile->delete();

        return redirect()
            ->route('admin.patients.show', ['patient' => $patient->id, 'tab' => 'files'])
            ->with('success', __('admin.patients.medical_file_deleted'));
    }
}

