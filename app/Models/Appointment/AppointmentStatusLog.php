<?php

namespace App\Models\Appointment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class AppointmentStatusLog
 *
 * Tracks status changes for appointments.
 *
 * @property int $appointment_id
 * @property string|null $old_status
 * @property string $new_status
 * @property string|null $changed_by_type
 * @property int|null $changed_by_id
 * @property string|null $notes
 * @property \Carbon\Carbon $created_at
 */
class AppointmentStatusLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'appointment_id',
        'old_status',
        'new_status',
        'changed_by_type',
        'changed_by_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    // ==================== Relationships ====================

    /**
     * The appointment this log belongs to.
     */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }
}

