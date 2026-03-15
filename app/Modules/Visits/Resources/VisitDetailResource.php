<?php

namespace App\Modules\Visits\Services;

use App\Enums\AppointmentStatus;
use App\Enums\VisitStatus;
use App\Models\Visit\Visit;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class VisitService
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Visit::query()
            ->with(['patient', 'doctor', 'appointment'])
            ->latest('id')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): Visit
    {
        return Visit::query()
            ->with(['patient', 'doctor', 'appointment', 'notes.createdBy'])
            ->findOrFail($id);
    }

    public function startVisit(Visit $visit, ?string $clinicalNotes = null): Visit
    {
        return DB::transaction(function () use ($visit, $clinicalNotes): Visit {
            $visit->refresh();

            if (($visit->status?->value ?? $visit->status) === VisitStatus::Completed->value) {
                throw new \DomainException('Completed visit cannot be started again.');
            }

            $visit->status = VisitStatus::WithDoctor;
            $visit->start_at = $visit->start_at ?? now();

            if ($clinicalNotes !== null) {
                $visit->clinical_notes = $clinicalNotes;
            }

            $visit->save();

            if ($visit->appointment) {
                $visit->appointment->status = AppointmentStatus::InProgress;
                $visit->appointment->save();
            }

            return $visit->load(['patient', 'doctor', 'appointment', 'notes.createdBy']);
        });
    }

    public function completeVisit(
        Visit $visit,
        ?string $diagnosis,
        ?string $clinicalNotes,
        ?string $internalNotes
    ): Visit {
        return DB::transaction(function () use ($visit, $diagnosis, $clinicalNotes, $internalNotes): Visit {
            $visit->refresh();

            $visit->status = VisitStatus::Completed;
            $visit->end_at = now();
            $visit->start_at = $visit->start_at ?? now();
            $visit->diagnosis = $diagnosis ?? $visit->diagnosis;
            $visit->clinical_notes = $clinicalNotes ?? $visit->clinical_notes;
            $visit->internal_notes = $internalNotes ?? $visit->internal_notes;
            $visit->save();

            if ($visit->appointment) {
                $visit->appointment->status = AppointmentStatus::Completed;
                $visit->appointment->save();
            }

            return $visit->load(['patient', 'doctor', 'appointment', 'notes.createdBy']);
        });
    }
}
