<?php

namespace App\Models\Medical;

use App\Enums\TreatmentPlanItemStatus;
use App\Models\Billing\InvoiceItem;
use App\Models\Clinic\Service;
use App\Models\Concerns\HasStatus;
use App\Models\Visit\Visit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class TreatmentPlanItem
 *
 * Individual items/procedures in a treatment plan.
 *
 * @property int $treatment_plan_id
 * @property int|null $service_id
 * @property int|null $tooth_number
 * @property string $title
 * @property string|null $description
 * @property int|null $session_no
 * @property float|null $estimated_cost
 * @property TreatmentPlanItemStatus $status
 * @property \Carbon\Carbon|null $planned_date
 * @property int|null $completed_visit_id
 */
class TreatmentPlanItem extends Model
{
    use HasFactory, HasStatus;

    protected $fillable = [
        'treatment_plan_id',
        'service_id',
        'tooth_number',
        'title',
        'description',
        'session_no',
        'estimated_cost',
        'status',
        'planned_date',
        'completed_visit_id',
    ];

    protected function casts(): array
    {
        return [
            'estimated_cost' => 'decimal:2',
            'planned_date' => 'date',
            'status' => TreatmentPlanItemStatus::class,
        ];
    }

    // ==================== Relationships ====================

    /**
     * The treatment plan this item belongs to.
     */
    public function treatmentPlan(): BelongsTo
    {
        return $this->belongsTo(TreatmentPlan::class);
    }

    /**
     * The service for this treatment item.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * The visit where this item was completed.
     */
    public function completedVisit(): BelongsTo
    {
        return $this->belongsTo(Visit::class, 'completed_visit_id');
    }

    /**
     * Invoice items related to this treatment plan item.
     */
    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }
}

