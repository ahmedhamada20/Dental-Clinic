<?php

namespace App\Models\Visit;

use App\Models\Patient\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class VisitNote
 *
 * Generic clinical note recorded during a visit.
 * Supports all medical specialties.
 *
 * @property int    $visit_id
 * @property int|null $doctor_id
 * @property int|null $patient_id
 * @property string|null $diagnosis
 * @property string $note
 * @property string|null $treatment_plan
 * @property \Carbon\Carbon|null $follow_up_date
 * @property array|null $attachments
 * @property int $created_by
 * @property int|null $updated_by
 */
class VisitNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'visit_id',
        'doctor_id',
        'patient_id',
        'diagnosis',
        'note',
        'treatment_plan',
        'follow_up_date',
        'attachments',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'follow_up_date' => 'date',
            'attachments'    => 'array',
        ];
    }

    // ==================== Relationships ====================

    /**
     * The visit this note belongs to.
     */
    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    /**
     * The doctor who authored this note.
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    /**
     * The patient this note concerns.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    /**
     * The user who created this note.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * The user who last updated this note.
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
