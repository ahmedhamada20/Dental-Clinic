<?php

namespace App\Models\Billing;

use App\Enums\DiscountType;
use App\Enums\InvoiceStatus;
use App\Models\Concerns\HasCode;
use App\Models\Concerns\HasStatus;
use App\Models\Patient\Patient;
use App\Models\User;
use App\Models\Visit\Visit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Invoice
 *
 * Invoice for services provided to a patient.
 *
 * @property string $invoice_no
 * @property int $patient_id
 * @property int|null $visit_id
 * @property int $created_by
 * @property float $subtotal
 * @property DiscountType|null $discount_type
 * @property float|null $discount_value
 * @property float|null $discount_amount
 * @property float $total
 * @property float $paid_amount
 * @property float $remaining_amount
 * @property InvoiceStatus $status
 * @property int|null $promotion_id
 * @property string|null $notes
 * @property \Carbon\Carbon $issued_at
 */
class Invoice extends Model
{
    use HasFactory, HasStatus, HasCode;

    protected $fillable = [
        'invoice_no',
        'patient_id',
        'visit_id',
        'created_by',
        'subtotal',
        'discount_type',
        'discount_value',
        'discount_amount',
        'total',
        'paid_amount',
        'remaining_amount',
        'status',
        'promotion_id',
        'notes',
        'issued_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount_value' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'remaining_amount' => 'decimal:2',
            'issued_at' => 'datetime',
            'status' => InvoiceStatus::class,
            'discount_type' => DiscountType::class,
        ];
    }

    // ==================== Scopes ====================

    /**
     * Scope to get paid invoices.
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope to get unpaid or partially paid invoices.
     */
    public function scopeUnpaid($query)
    {
        return $query->whereIn('status', [
            InvoiceStatus::UNPAID->value,
            InvoiceStatus::PARTIALLY_PAID->value,
        ]);
    }

    // ==================== Relationships ====================

    /**
     * The patient for this invoice.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * The visit associated with this invoice.
     */
    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    /**
     * The user who created this invoice.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * The promotion applied to this invoice.
     */
    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    /**
     * Items in this invoice.
     */
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Payments for this invoice.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->orderByDesc('payment_date');
    }

    /**
     * Payment allocations for this invoice.
     */
    public function paymentAllocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    /**
     * Check if the invoice has any payments.
     */
    public function getHasPaymentsAttribute(): bool
    {
        return (float) $this->paid_amount > 0 || $this->payments()->exists();
    }

    /**
     * Check if the invoice can be edited.
     */
    public function getCanBeEditedAttribute(): bool
    {
        $status = $this->status instanceof InvoiceStatus
            ? $this->status
            : InvoiceStatus::tryFrom((string) $this->status);

        if (! $status) {
            return false;
        }

        return ! in_array($status, [InvoiceStatus::CANCELLED, InvoiceStatus::PAID], true);
    }
}
