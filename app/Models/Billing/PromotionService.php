<?php

namespace App\Models\Billing;

use App\Models\Clinic\Service;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class PromotionService
 *
 * Junction table linking promotions to services.
 *
 * @property int $promotion_id
 * @property int $service_id
 */
class PromotionService extends Model
{
    use HasFactory;

    protected $fillable = [
        'promotion_id',
        'service_id',
    ];

    // ==================== Relationships ====================

    /**
     * The promotion for this junction.
     */
    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    /**
     * The service for this junction.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}

