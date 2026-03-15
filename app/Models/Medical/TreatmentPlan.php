<?php

namespace App\Models\Medical;

use App\Enums\TreatmentPlanStatus;
use App\Models\Concerns\HasStatus;
use App\Models\Patient\Patient;
use App\Models\User;
use App\Models\Visit\Visit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class TreatmentPlan
 *
 * Treatment plan created for a patient's dental care.
 *
 * @property int $patient_id
 * @property int|null $doctor_id
 * @property int|null $visit_id
 * @property string $title
 * @property string|null $description
 * @property float|null $estimated_total
 * @property TreatmentPlanStatus $status
 * @property \Carbon\Carbon|null $start_date
 * @property \Carbon\Carbon|null $end_date
 */
class TreatmentPlan extends Model
{
    use HasFactory, HasStatus;

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'visit_id',
        'title',
        'description',
        'estimated_total',
        'status',
        'start_date',
        'end_date',
    ];

    protected function casts(): array
    {
        return [
            'estimated_total' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
            'status' => TreatmentPlanStatus::class,
        ];
    }

    // ==================== Relationships ====================

    /**
     * The patient this treatment plan is for.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * The doctor who created this treatment plan.
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    /**
     * The visit this treatment plan was created from.
     */
    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    /**
     * Items in this treatment plan.
     */
    public function items(): HasMany
    {
        return $this->hasMany(TreatmentPlanItem::class);
    }
}

