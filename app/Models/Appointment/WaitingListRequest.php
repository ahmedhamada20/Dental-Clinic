<?php

namespace App\Models\Appointment;

use App\Enums\WaitingListStatus;
use App\Models\Clinic\Service;
use App\Models\Concerns\HasStatus;
use App\Models\Patient\Patient;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class WaitingListRequest
 *
 * Represents a patient's request to be added to a waiting list.
 *
 * @property int $patient_id
 * @property int|null $service_id
 * @property \Carbon\Carbon|null $preferred_date
 * @property string|null $preferred_from_time
 * @property string|null $preferred_to_time
 * @property WaitingListStatus $status
 * @property \Carbon\Carbon|null $notified_at
 * @property \Carbon\Carbon|null $expires_at
 * @property int|null $booked_appointment_id
 */
class WaitingListRequest extends Model
{
    use HasFactory, HasStatus;

    protected $fillable = [
        'patient_id',
        'service_id',
        'preferred_date',
        'preferred_from_time',
        'preferred_to_time',
        'status',
        'notified_at',
        'expires_at',
        'booked_appointment_id',
    ];

    protected function casts(): array
    {
        return [
            'preferred_date' => 'date',
            'notified_at' => 'datetime',
            'expires_at' => 'datetime',
            'status' => WaitingListStatus::class,
        ];
    }

    // ==================== Relationships ====================

    /**
     * The patient requesting the waiting list slot.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * The service for this waiting list request.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * The appointment booked from this waiting list request.
     */
    public function bookedAppointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'booked_appointment_id');
    }
}

