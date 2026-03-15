<?php

namespace App\Models\Billing;

use App\Enums\PromotionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Promotion
 *
 * Promotional discounts and special offers.
 *
 * @property string $title_ar
 * @property string $title_en
 * @property string $code
 * @property PromotionType $promotion_type
 * @property float $value
 * @property bool $applies_once
 * @property \Carbon\Carbon $starts_at
 * @property \Carbon\Carbon $ends_at
 * @property bool $is_active
 * @property string|null $notes
 */
class Promotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'title_ar',
        'title_en',
        'code',
        'promotion_type',
        'value',
        'applies_once',
        'starts_at',
        'ends_at',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'applies_once' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
            'promotion_type' => PromotionType::class,
        ];
    }

    // ==================== Scopes ====================

    /**
     * Scope to get promotions active now.
     */
    public function scopeActiveNow($query)
    {
        return $query
            ->where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now());
    }

    // ==================== Relationships ====================

    /**
     * Promotion services for this promotion.
     */
    public function promotionServices(): HasMany
    {
        return $this->hasMany(PromotionService::class);
    }

    /**
     * Services included in this promotion.
     */
    public function services()
    {
        return $this->belongsToMany(
            \App\Models\Clinic\Service::class,
            'promotion_services',
            'promotion_id',
            'service_id'
        );
    }

    /**
     * Invoices using this promotion.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}

