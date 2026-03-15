<?php

namespace App\Models\Clinic;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class WorkingHour
 *
 * Working hour slots for a specific working day.
 *
 * @property int $working_day_id
 * @property string $start_time
 * @property string $end_time
 * @property int|null $max_patients_per_day
 * @property int|null $slot_granularity_minutes
 */
class WorkingHour extends Model
{
    use HasFactory;

    protected $fillable = [
        'working_day_id',
        'start_time',
        'end_time',
        'max_patients_per_day',
        'slot_granularity_minutes',
    ];

    // ==================== Relationships ====================

    /**
     * The working day this hour belongs to.
     */
    public function workingDay(): BelongsTo
    {
        return $this->belongsTo(WorkingDay::class);
    }
}

