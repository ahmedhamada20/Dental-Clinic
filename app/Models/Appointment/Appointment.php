<?php

namespace App\Models\Appointment;

use App\Enums\AppointmentStatus;
use App\Enums\BookingSource;
use App\Models\Clinic\MedicalSpecialty;
use App\Models\Clinic\Service;
use App\Models\Concerns\HasCode;
use App\Models\Concerns\HasStatus;
use App\Models\Patient\Patient;
use App\Models\User;
use App\Models\Visit\Visit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

/**
 * Class Appointment
 *
 * Represents an appointment booking in the clinic.
 *
 * @property string $appointment_no
 * @property int $patient_id
 * @property int|null $service_id
 * @property int|null $assigned_doctor_id
 * @property \Carbon\Carbon $appointment_date
 * @property string|null $start_time
 * @property string|null $end_time
 * @property AppointmentStatus $status
 * @property BookingSource|null $booking_source
 * @property string|null $cancellation_reason
 * @property \Carbon\Carbon|null $cancelled_at
 * @property string|null $cancelled_by_type
 * @property int|null $cancelled_by_id
 * @property \Carbon\Carbon|null $confirmed_at
 * @property \Carbon\Carbon|null $checked_in_at
 * @property string|null $notes
 */
class Appointment extends Model
{
    use HasFactory, HasStatus, HasCode;

    protected $fillable = [
        'appointment_no',
        'patient_id',
        'specialty_id',
        'service_id',
        'assigned_doctor_id',
        'appointment_date',
        'start_time',
        'end_time',
        'status',
        'booking_source',
        'cancellation_reason',
        'cancelled_at',
        'cancelled_by_type',
        'cancelled_by_id',
        'confirmed_at',
        'checked_in_at',
        'notes',
    ];

    protected $appends = [
        'doctor_id',
        'appointment_time',
    ];

    protected function casts(): array
    {
        return [
            'appointment_date' => 'date',
            'cancelled_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'checked_in_at' => 'datetime',
            'status' => AppointmentStatus::class,
            'booking_source' => BookingSource::class,
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $appointment): void {
            if (filled($appointment->appointment_no)) {
                return;
            }

            do {
                $number = 'APT-' . strtoupper(Str::random(8));
            } while (self::query()->where('appointment_no', $number)->exists());

            $appointment->appointment_no = $number;
        });
    }

    // ==================== Scopes ====================

    /**
     * Scope to get upcoming appointments.
     */
    public function scopeUpcoming($query)
    {
        return $query->whereDate('appointment_date', '>=', now()->toDateString());
    }

    /**
     * Scope to get pending appointments.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get confirmed appointments.
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    // ==================== Relationships ====================

    /**
     * The patient for this appointment.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * The service for this appointment.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * The doctor assigned to this appointment.
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_doctor_id');
    }

    /**
     * The specialty for this appointment.
     */
    public function specialty(): BelongsTo
    {
        return $this->belongsTo(MedicalSpecialty::class, 'specialty_id');
    }

    /**
     * Status logs for this appointment.
     */
    public function statusLogs(): HasMany
    {
        return $this->hasMany(AppointmentStatusLog::class);
    }

    /**
     * The visit created from this appointment.
     */
    public function visit(): HasOne
    {
        return $this->hasOne(Visit::class);
    }

    /**
     * The visit ticket for this appointment.
     */
    public function ticket(): HasOne
    {
        return $this->hasOne(VisitTicket::class);
    }

    public function getDoctorIdAttribute(): ?int
    {
        return $this->assigned_doctor_id;
    }

    public function setDoctorIdAttribute($value): void
    {
        $this->attributes['assigned_doctor_id'] = $value;
    }

    public function getAppointmentTimeAttribute(): ?string
    {
        return $this->start_time;
    }

    public function setAppointmentTimeAttribute($value): void
    {
        $this->attributes['start_time'] = $value;
    }
}
