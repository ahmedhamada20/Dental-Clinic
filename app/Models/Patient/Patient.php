<?php

namespace App\Models\Patient;

use App\Enums\PatientStatus;
use App\Models\Appointment\Appointment;
use App\Models\Appointment\VisitTicket;
use App\Models\Appointment\WaitingListRequest;
use App\Models\Billing\Invoice;
use App\Models\Billing\Payment;
use App\Models\Concerns\HasCode;
use App\Models\Medical\MedicalFile;
use App\Models\Medical\OdontogramHistory;
use App\Models\Medical\OdontogramTooth;
use App\Models\Medical\Prescription;
use App\Models\Medical\TreatmentPlan;
use App\Models\System\DeviceToken;
use App\Models\Visit\Visit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;

/**
 * Class Patient
 *
 * Represents a patient in the dental clinic system.
 *
 * @property string $patient_code
 * @property string $first_name
 * @property string $last_name
 * @property string $full_name
 * @property string $phone
 * @property string|null $alternate_phone
 * @property string $email
 * @property string|null $password
 * @property string $gender
 * @property \Carbon\Carbon $date_of_birth
 * @property int|null $age
 * @property string $address
 * @property string $city
 * @property string|null $profile_image
 * @property PatientStatus $status
 * @property string|null $registered_from
 * @property \Carbon\Carbon|null $last_login_at
 */
class Patient extends Model
{
    use HasApiTokens, HasFactory, HasCode;

    protected $fillable = [
        'patient_code',
        'first_name',
        'last_name',
        'full_name',
        'phone',
        'alternate_phone',
        'email',
        'password',
        'gender',
        'date_of_birth',
        'age',
        'address',
        'city',
        'profile_image',
        'status',
        'registered_from',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'last_login_at' => 'datetime',
            'status' => PatientStatus::class,
            'password' => 'hashed',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $patient): void {
            if (filled($patient->patient_code)) {
                return;
            }

            do {
                $code = 'PAT-' . strtoupper(Str::random(8));
            } while (self::query()->where('patient_code', $code)->exists());

            $patient->patient_code = $code;
        });
    }

    /**
     * Get the patient's display name.
     *
     * @return string
     */
    public function getDisplayNameAttribute(): string
    {
        return trim($this->full_name ?: "{$this->first_name} {$this->last_name}");
    }

    // ==================== Scopes ====================

    /**
     * Scope to get only active patients.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // ==================== Relationships ====================

    /**
     * Patient's profile information.
     */
    public function profile(): HasOne
    {
        return $this->hasOne(PatientProfile::class);
    }

    /**
     * Patient's medical history.
     */
    public function medicalHistory(): HasOne
    {
        return $this->hasOne(PatientMedicalHistory::class);
    }

    /**
     * Backward-compatible alias for legacy usages expecting a hasMany relationship.
     */
    public function medicalHistories(): HasMany
    {
        return $this->hasMany(PatientMedicalHistory::class);
    }

    /**
     * Emergency contacts for this patient.
     */
    public function emergencyContacts(): HasMany
    {
        return $this->hasMany(EmergencyContact::class);
    }

    /**
     * Appointments for this patient.
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Waiting list requests for this patient.
     */
    public function waitingListRequests(): HasMany
    {
        return $this->hasMany(WaitingListRequest::class);
    }

    /**
     * Visits for this patient.
     */
    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }

    /**
     * Visit tickets for this patient.
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(VisitTicket::class);
    }

    /**
     * Odontogram teeth records for this patient.
     */
    public function odontogramTeeth(): HasMany
    {
        return $this->hasMany(OdontogramTooth::class);
    }

    /**
     * Odontogram history records for this patient.
     */
    public function odontogramHistory(): HasMany
    {
        return $this->hasMany(OdontogramHistory::class);
    }

    /**
     * Treatment plans for this patient.
     */
    public function treatmentPlans(): HasMany
    {
        return $this->hasMany(TreatmentPlan::class);
    }

    /**
     * Prescriptions for this patient.
     */
    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    /**
     * Medical files for this patient.
     */
    public function medicalFiles(): HasMany
    {
        return $this->hasMany(MedicalFile::class);
    }

    /**
     * Invoices for this patient.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Payments made by this patient.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Device tokens for push notifications.
     */
    public function deviceTokens(): HasMany
    {
        return $this->hasMany(DeviceToken::class);
    }

    public function getAuthIdentifier(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }


}

