<?php

namespace App\Models\Medical;

use App\Enums\ToothStatus;
use App\Models\Patient\Patient;
use App\Models\User;
use App\Models\Visit\Visit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class OdontogramTooth
 *
 * Current state of teeth in the odontogram system.
 *
 * @property int $patient_id
 * @property int $tooth_number
 * @property ToothStatus $status
 * @property string|null $surface
 * @property string|null $notes
 * @property int|null $last_updated_by
 * @property int|null $visit_id
 */
class OdontogramTooth extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'tooth_number',
        'status',
        'surface',
        'notes',
        'last_updated_by',
        'visit_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => ToothStatus::class,
        ];
    }

    // ==================== Scopes ====================

    /**
     * Scope to filter teeth for a specific patient.
     */
    public function scopeForPatient($query, $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    // ==================== Relationships ====================

    /**
     * The patient this tooth record belongs to.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * The user who last updated this tooth record.
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_updated_by');
    }

    /**
     * The visit where this tooth was updated.
     */
    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }
}

