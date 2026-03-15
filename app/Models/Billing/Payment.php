<?php

namespace App\Models\Billing;

use App\Enums\PaymentMethod;
use App\Models\Concerns\HasCode;
use App\Models\Patient\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Payment
 *
 * Payment received from a patient.
 *
 * @property string $payment_no
 * @property int $patient_id
 * @property int|null $invoice_id
 * @property int $received_by
 * @property PaymentMethod $payment_method
 * @property float $amount
 * @property string|null $reference_no
 * @property \Carbon\Carbon $payment_date
 * @property string|null $notes
 */
class Payment extends Model
{
    use HasFactory, HasCode;

    protected $fillable = [
        'payment_no',
        'patient_id',
        'invoice_id',
        'received_by',
        'payment_method',
        'amount',
        'reference_no',
        'payment_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_date' => 'datetime',
            'payment_method' => PaymentMethod::class,
        ];
    }

    // ==================== Relationships ====================

    /**
     * The patient who made this payment.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * The invoice this payment is for.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * The user who received this payment.
     */
    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    /**
     * Payment allocations for this payment.
     */
    public function allocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class);
    }
}

