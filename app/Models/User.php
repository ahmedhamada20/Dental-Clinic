<?php

namespace App\Models;

use App\Enums\UserStatus;
use App\Enums\UserType;
use App\Models\Clinic\MedicalSpecialty;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * Class User
 *
 * Represents dashboard users: admin, doctor, receptionist, assistant.
 *
 * @property string $first_name
 * @property string $last_name
 * @property string $full_name
 * @property string $email
 * @property string $phone
 * @property string $password
 * @property UserType $user_type
 * @property UserStatus $status
 * @property \Carbon\Carbon|null $last_login_at
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected string $guard_name = 'web';

    protected $fillable = [
        'first_name',
        'last_name',
        'full_name',
        'email',
        'phone',
        'password',
        'user_type',
        'specialty_id',
        'status',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'user_type' => UserType::class,
            'status' => UserStatus::class,
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Specialty assigned to this user.
     */
    public function specialty(): BelongsTo
    {
        return $this->belongsTo(MedicalSpecialty::class, 'specialty_id');
    }

    /**
     * Specialty relation scoped for doctor users only.
     */
    public function doctorSpecialty(): BelongsTo
    {
        return $this->belongsTo(MedicalSpecialty::class, 'specialty_id')
            ->where('users.user_type', UserType::DOCTOR->value);
    }

    /**
     * Get the user's display name.
     *
     * @return string
     */
    public function getDisplayNameAttribute(): string
    {
        return trim($this->full_name ?: "{$this->first_name} {$this->last_name}");
    }

    /**
     * Determine whether this user is a doctor.
     */
    public function isDoctor(): bool
    {
        return $this->user_type === UserType::DOCTOR;
    }

    // ==================== Relationships ====================

    /**
     * Appointments assigned to this doctor.
     */
    public function assignedAppointments(): HasMany
    {
        return $this->hasMany(\App\Models\Appointment\Appointment::class, 'assigned_doctor_id');
    }

    /**
     * Visits conducted by this doctor.
     */
    public function doctorVisits(): HasMany
    {
        return $this->hasMany(\App\Models\Visit\Visit::class, 'doctor_id');
    }

    /**
     * Visits checked in by this user.
     */
    public function checkedInVisits(): HasMany
    {
        return $this->hasMany(\App\Models\Visit\Visit::class, 'checked_in_by');
    }

    /**
     * Visit notes created by this user.
     */
    public function createdVisitNotes(): HasMany
    {
        return $this->hasMany(\App\Models\Visit\VisitNote::class, 'created_by');
    }

    /**
     * Medical histories updated by this user.
     */
    public function updatedMedicalHistories(): HasMany
    {
        return $this->hasMany(\App\Models\Patient\PatientMedicalHistory::class, 'updated_by');
    }

    /**
     * Dental-module compatibility relation for odontogram teeth updated by this user.
     * Core workflows should prefer generic visit and record relationships.
     */
    public function updatedOdontogramTeeth(): HasMany
    {
        return $this->hasMany(\App\Models\Medical\OdontogramTooth::class, 'last_updated_by');
    }

    /**
     * Dental-module compatibility relation for odontogram changes made by this user.
     * Core workflows should prefer generic visit and record relationships.
     */
    public function odontogramChanges(): HasMany
    {
        return $this->hasMany(\App\Models\Medical\OdontogramHistory::class, 'changed_by');
    }

    /**
     * Treatment plans created by this doctor.
     */
    public function treatmentPlans(): HasMany
    {
        return $this->hasMany(\App\Models\Medical\TreatmentPlan::class, 'doctor_id');
    }

    /**
     * Prescriptions issued by this doctor.
     */
    public function prescriptions(): HasMany
    {
        return $this->hasMany(\App\Models\Medical\Prescription::class, 'doctor_id');
    }

    /**
     * Medical files uploaded by this user.
     */
    public function uploadedMedicalFiles(): HasMany
    {
        return $this->hasMany(\App\Models\Medical\MedicalFile::class, 'uploaded_by');
    }

    /**
     * Invoices created by this user.
     */
    public function createdInvoices(): HasMany
    {
        return $this->hasMany(\App\Models\Billing\Invoice::class, 'created_by');
    }

    /**
     * Payments received by this user.
     */
    public function receivedPayments(): HasMany
    {
        return $this->hasMany(\App\Models\Billing\Payment::class, 'received_by');
    }
}
