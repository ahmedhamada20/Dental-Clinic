<?php

namespace App\Models\Patient;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class PatientMedicalHistory
 *
 * Core patient medical history for shared cross-specialty information.
 * Specialty-specific longitudinal records should live in optional modules
 * (for example dental tooth history, dermatology image timelines, or
 * ophthalmology pressure/vision records) instead of expanding this model.
 *
 * @property int $patient_id
 * @property string|null $allergies
 * @property string|null $chronic_diseases
 * @property string|null $current_medications
 * @property string|null $medical_notes
 * @property string|null $dental_history Legacy specialty field retained for compatibility.
 * @property string|null $important_alerts
 * @property int|null $updated_by
 */
class PatientMedicalHistory extends Model
{
    use HasFactory;

    protected $table = 'patient_medical_histories';

    protected $fillable = [
        'patient_id',
        'allergies',
        'chronic_diseases',
        'current_medications',
        'medical_notes',
        'dental_history',
        'important_alerts',
        'updated_by',
    ];

    // ==================== Relationships ====================

    /**
     * The patient this medical history belongs to.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * The user who last updated this medical history.
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
