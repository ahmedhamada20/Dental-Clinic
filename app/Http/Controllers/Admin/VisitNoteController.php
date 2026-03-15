<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Policies\VisitNotePolicy;
use App\Models\Visit\Visit;
use App\Models\Visit\VisitNote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VisitNoteController extends Controller
{
    public function store(Request $request, Visit $visit): RedirectResponse
    {
        abort_unless(app(VisitNotePolicy::class)->create($request->user()), 403);

        $validated = $this->validateNote($request);

        $visit->notes()->create(array_merge($validated, [
            'doctor_id'  => $validated['doctor_id'] ?? $request->user()->id,
            'patient_id' => $validated['patient_id'] ?? $visit->patient_id,
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]));

        return redirect()
            ->route('admin.visits.show', ['visit' => $visit->id, 'tab' => 'notes'])
            ->with('success', 'Visit note added successfully.');
    }

    public function update(Request $request, Visit $visit, VisitNote $visitNote): RedirectResponse
    {
        abort_unless($visitNote->visit_id === $visit->id, 404);
        abort_unless(app(VisitNotePolicy::class)->update($request->user(), $visitNote), 403);

        $validated = $this->validateNote($request);

        $visitNote->update(array_merge($validated, [
            'updated_by' => $request->user()->id,
        ]));

        return redirect()
            ->route('admin.visits.show', ['visit' => $visit->id, 'tab' => 'notes'])
            ->with('success', 'Visit note updated successfully.');
    }

    public function destroy(Request $request, Visit $visit, VisitNote $visitNote): RedirectResponse
    {
        abort_unless($visitNote->visit_id === $visit->id, 404);
        abort_unless(app(VisitNotePolicy::class)->delete($request->user(), $visitNote), 403);

        $visitNote->delete();

        return redirect()
            ->route('admin.visits.show', ['visit' => $visit->id, 'tab' => 'notes'])
            ->with('success', 'Visit note deleted successfully.');
    }

    protected function validateNote(Request $request): array
    {
        return $request->validate([
            'doctor_id'      => ['nullable', 'integer', 'exists:users,id'],
            'patient_id'     => ['nullable', 'integer', 'exists:patients,id'],
            'diagnosis'      => ['nullable', 'string', 'max:5000'],
            'note'           => ['required', 'string', 'max:10000'],
            'treatment_plan' => ['nullable', 'string', 'max:5000'],
            'follow_up_date' => ['nullable', 'date', 'after_or_equal:today'],
            'attachments'    => ['nullable', 'array'],
            'attachments.*'  => ['string', 'max:500'],
        ]);
    }
}
