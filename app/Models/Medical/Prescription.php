<?php

namespace App\Models\Medical;

use App\Models\Patient\Patient;
use App\Models\User;
use App\Models\Visit\Visit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Prescription
 *
 * Prescription issued to a patient during a visit.
 *
 * @property int $patient_id
 * @property int|null $visit_id
 * @property int|null $doctor_id
 * @property string|null $notes
 * @property \Carbon\Carbon $issued_at
 */
class Prescription extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'visit_id',
        'doctor_id',
        'notes',
        'issued_at',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'datetime',
        ];
    }

    // ==================== Relationships ====================

    /**
     * The patient this prescription is for.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * The visit during which this prescription was issued.
     */
    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    /**
     * The doctor who issued this prescription.
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    /**
     * Items in this prescription.
     */
    public function items(): HasMany
    {
        return $this->hasMany(PrescriptionItem::class);
    }
}

