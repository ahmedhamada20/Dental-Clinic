<?php

namespace App\Models\Medical;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class PrescriptionItem
 *
 * Individual medicine items in a prescription.
 *
 * @property int $prescription_id
 * @property string $medicine_name
 * @property string|null $dosage
 * @property string|null $frequency
 * @property string|null $dose_duration
 * @property string|null $treatment_duration
 * @property string|null $duration
 * @property string|null $instructions
 */
class PrescriptionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'prescription_id',
        'medicine_name',
        'dosage',
        'frequency',
        'dose_duration',
        'treatment_duration',
        'duration',
        'instructions',
    ];

    // ==================== Relationships ====================

    /**
     * The prescription this item belongs to.
     */
    public function prescription(): BelongsTo
    {
        return $this->belongsTo(Prescription::class);
    }
}

