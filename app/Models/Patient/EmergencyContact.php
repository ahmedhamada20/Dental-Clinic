<?php

namespace App\Models\Patient;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class EmergencyContact
 *
 * Emergency contact information for patients.
 *
 * @property int $patient_id
 * @property string $name
 * @property string $relation
 * @property string $phone
 * @property string|null $notes
 */
class EmergencyContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'name',
        'relation',
        'phone',
        'notes',
    ];

    // ==================== Relationships ====================

    /**
     * The patient this emergency contact belongs to.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
}

