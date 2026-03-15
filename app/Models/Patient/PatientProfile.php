<?php

namespace App\Models\Patient;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class PatientProfile
 *
 * Extended profile information for patients.
 *
 * @property int $patient_id
 * @property string|null $occupation
 * @property string|null $marital_status
 * @property string|null $preferred_language
 * @property string|null $blood_group
 * @property string|null $notes
 */
class PatientProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'occupation',
        'marital_status',
        'preferred_language',
        'blood_group',
        'notes',
    ];

    // ==================== Relationships ====================

    /**
     * The patient that owns this profile.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
}

