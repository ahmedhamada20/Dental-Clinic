<?php

namespace App\Models\Concerns;

use App\Models\Patient\Patient;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Trait BelongsToPatient
 *
 * For models that have a patient_id foreign key.
 * Applied to: Appointment, Visit, TreatmentPlan, Prescription, Invoice, OdontogramTooth, etc.
 */
trait BelongsToPatient
{
    /**
     * Get the patient this model belongs to.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Scope to filter records for a specific patient.
     */
    public function scopeForPatient($query, $patientId)
    {
        return $query->where('patient_id', $patientId);
    }
}

