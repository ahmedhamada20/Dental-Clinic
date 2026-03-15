<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ToothStatus;
use App\Http\Controllers\Controller;
use App\Policies\OdontogramPolicy;
use App\Models\Medical\OdontogramHistory;
use App\Models\Medical\OdontogramTooth;
use App\Models\Visit\Visit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OdontogramController extends Controller
{
    public function store(Request $request, Visit $visit): RedirectResponse
    {
        abort_unless(app(OdontogramPolicy::class)->update($request->user()), 403);

        $validated = $request->validate([
            'tooth_number' => ['required', 'string', 'max:10'],
            'status' => ['required', Rule::in(collect(ToothStatus::cases())->map->value->all())],
            'surface' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $visit->loadMissing(['patient', 'appointment']);
        $patientId = $visit->patient_id
            ?? $visit->patient?->id
            ?? $visit->appointment?->patient_id;

        if (! $patientId) {
            return redirect()
                ->route('admin.visits.show', ['visit' => $visit->id, 'tab' => 'odontogram'])
                ->with('error', __('admin.messages.odontogram.patient_not_found'));
        }

        DB::transaction(function () use ($visit, $validated, $request, $patientId) {
            $tooth = OdontogramTooth::query()->firstOrNew([
                'patient_id' => $patientId,
                'tooth_number' => $validated['tooth_number'],
            ]);

            $oldStatus = $tooth->exists
                ? ($tooth->status instanceof ToothStatus ? $tooth->status->value : (string) $tooth->status)
                : null;

            $tooth->fill([
                'status' => $validated['status'],
                'surface' => $validated['surface'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'visit_id' => $visit->id,
                'last_updated_by' => $request->user()->id,
            ])->save();

            $history = new OdontogramHistory([
                'patient_id' => $patientId,
                'tooth_number' => $validated['tooth_number'],
                'old_status' => $oldStatus,
                'new_status' => $validated['status'],
                'surface' => $validated['surface'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'visit_id' => $visit->id,
                'changed_by' => $request->user()->id,
            ]);
            $history->created_at = now();
            $history->save();
        });

        return redirect()
            ->route('admin.visits.show', ['visit' => $visit->id, 'tab' => 'odontogram'])
            ->with('success', __('admin.messages.odontogram.saved'));
    }
}
