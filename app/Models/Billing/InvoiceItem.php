<?php

namespace App\Models\Billing;

use App\Models\Clinic\Service;
use App\Models\Medical\TreatmentPlanItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class InvoiceItem
 *
 * Individual line items in an invoice.
 *
 * @property int $invoice_id
 * @property int|null $service_id
 * @property int|null $treatment_plan_item_id
 * @property string $item_type
 * @property string $item_name_ar
 * @property string $item_name_en
 * @property string|null $description
 * @property float $quantity
 * @property float $unit_price
 * @property float|null $discount_amount
 * @property float $total
 * @property int|null $tooth_number
 */
class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'service_id',
        'treatment_plan_item_id',
        'item_type',
        'item_name_ar',
        'item_name_en',
        'description',
        'quantity',
        'unit_price',
        'discount_amount',
        'total',
        'tooth_number',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    // ==================== Relationships ====================

    /**
     * The invoice this item belongs to.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * The service for this item.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * The treatment plan item related to this invoice item.
     */
    public function treatmentPlanItem(): BelongsTo
    {
        return $this->belongsTo(TreatmentPlanItem::class);
    }
}

