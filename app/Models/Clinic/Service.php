<?php

namespace App\Models\Clinic;

use App\Models\Appointment\Appointment;
use App\Models\Billing\Invoice;
use App\Models\Billing\Promotion;
use App\Models\Billing\PromotionService;
use App\Models\Medical\TreatmentPlanItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Service
 *
 * Clinic services offered by the clinic.
 *
 * @property int|null $category_id
 * @property string|null $code
 * @property string $name_ar
 * @property string|null $name_en
 * @property string|null $description_ar
 * @property string|null $description_en
 * @property float $default_price
 * @property int|null $duration_minutes
 * @property bool $is_bookable
 * @property bool $is_active
 * @property int|null $sort_order
 */
class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'code',
        'name_ar',
        'name_en',
        'description_ar',
        'description_en',
        'default_price',
        'duration_minutes',
        'is_bookable',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'default_price' => 'decimal:2',
            'is_bookable' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    // ==================== Scopes ====================

    /**
     * Scope to get only active services.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get only bookable services.
     */
    public function scopeBookable($query)
    {
        return $query->where('is_bookable', true);
    }

    // ==================== Relationships ====================

    /**
     * The category this service belongs to.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class);
    }

    /**
     * The specialty this service belongs to through its category.
     */
    public function medicalSpecialty(): HasOneThrough
    {
        return $this->hasOneThrough(
            MedicalSpecialty::class,
            ServiceCategory::class,
            'id',
            'id',
            'category_id',
            'medical_specialty_id'
        );
    }

    /**
     * Appointments for this service.
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Treatment plan items using this service.
     */
    public function treatmentPlanItems(): HasMany
    {
        return $this->hasMany(TreatmentPlanItem::class);
    }

    /**
     * Invoice items using this service.
     */
    public function invoiceItems(): HasMany
    {
        return $this->hasMany(\App\Models\Billing\InvoiceItem::class);
    }

    /**
     * Promotions for this service.
     */
    public function promotionServices(): HasMany
    {
        return $this->hasMany(PromotionService::class);
    }

    /**
     * The promotions that belong to the service.
     */
    public function promotions(): BelongsToMany
    {
        return $this->belongsToMany(
            Promotion::class,
            'promotion_services',
            'service_id',
            'promotion_id'
        );
    }
}

