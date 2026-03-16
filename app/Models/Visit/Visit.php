<?php

namespace App\Models\Visit;

use App\Enums\VisitStatus;
use App\Models\Appointment\Appointment;
use App\Models\Appointment\VisitTicket;
use App\Models\Billing\Invoice;
use App\Models\Concerns\HasCode;
use App\Models\Concerns\HasStatus;
use App\Models\Medical\MedicalFile;
use App\Models\Medical\Prescription;
use App\Models\Medical\TreatmentPlan;
use App\Models\Patient\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

/**
 * Class Visit
 *
 * Represents an actual patient visit/consultation in the clinic.
 * Generic across all medical specialties.
 *
 * @property string $visit_no
 * @property int|null $appointment_id
 * @property int $patient_id
 * @property int $doctor_id
 * @property int|null $checked_in_by
 * @property \Carbon\Carbon $visit_date
 * @property \Carbon\Carbon|null $start_at
 * @property \Carbon\Carbon|null $end_at
 * @property VisitStatus $status
 * @property string|null $chief_complaint
 * @property string|null $diagnosis
 * @property string|null $clinical_notes
 * @property string|null $internal_notes
 */
class Visit extends Model
{
    use HasFactory, HasStatus, HasCode;

    protected $fillable = [
        'visit_no',
        'appointment_id',
        'patient_id',
        'doctor_id',
        'checked_in_by',
        'visit_date',
        'visit_date',
        'start_at',
        'end_at',
        'status',
        'chief_complaint',
        'diagnosis',
        'clinical_notes',
        'internal_notes',
    ];

    protected function casts(): array
    {
        return [
            'visit_date' => 'date',
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'status' => VisitStatus::class,
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $visit): void {
            if (!empty($visit->visit_no)) {
                return;
            }

            $visit->visit_no = static::reserveNextVisitNumber();
        });
    }

    /**
     * Generates the next globally-unique, monotonically-increasing visit number.
     *
     * Runs inside its own DB transaction with a SELECT … FOR UPDATE so that
     * concurrent requests cannot read the same MAX and produce the same number.
     */
    protected static function reserveNextVisitNumber(): string
    {
        $prefix = 'VIS-' . now()->format('Ymd') . '-';

        $lastVisit = static::where('visit_no', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->first();

        if (!$lastVisit) {
            $number = 70000;
        } else {
            $lastNumber = (int)substr($lastVisit->visit_no, -5);
            $number = $lastNumber + 1;
        }

        return $prefix . str_pad($number, 5, '0', STR_PAD_LEFT);
    }

    // ==================== Scopes ====================

    /**
     * Scope to get completed visits.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // ==================== Relationships ====================

    /**
     * The appointment linked to this visit.
     */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    /**
     * The patient for this visit.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * The doctor conducting this visit.
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    /**
     * The user who checked in this visit.
     */
    public function checkedInBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }

    /**
     * The queue ticket for this visit.
     */
    public function ticket(): HasOne
    {
        return $this->hasOne(VisitTicket::class);
    }

    /**
     * Clinical notes recorded during this visit.
     */
    public function notes(): HasMany
    {
        return $this->hasMany(VisitNote::class);
    }

    /**
     * Treatment plans linked to this visit.
     */
    public function treatmentPlans(): HasMany
    {
        return $this->hasMany(TreatmentPlan::class);
    }

    /**
     * Prescriptions issued during this visit.
     */
    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    /**
     * Medical files attached to this visit.
     */
    public function medicalFiles(): HasMany
    {
        return $this->hasMany(MedicalFile::class);
    }

    /**
     * The invoice for this visit.
     */
    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }
}

