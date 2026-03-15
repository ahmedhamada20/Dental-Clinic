<?php

namespace App\Models\Clinic;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class WorkingDay
 *
 * Represents clinic working days (Monday-Sunday).
 *
 * @property int $day_of_week
 * @property bool $is_open
 */
class WorkingDay extends Model
{
    use HasFactory;

    protected $fillable = [
        'day_of_week',
        'is_open',
    ];

    protected function casts(): array
    {
        return [
            'is_open' => 'boolean',
        ];
    }

    // ==================== Relationships ====================

    /**
     * Working hours for this working day.
     */
    public function workingHours(): HasMany
    {
        return $this->hasMany(WorkingHour::class);
    }
}

