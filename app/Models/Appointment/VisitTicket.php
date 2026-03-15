<?php

namespace App\Models\Appointment;

use App\Casts\TicketStatusCast;
use App\Enums\TicketStatus;
use App\Models\Concerns\HasStatus;
use App\Models\Patient\Patient;
use App\Models\Visit\Visit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class VisitTicket
 *
 * Visit tickets for queuing system.
 *
 * @property \Carbon\Carbon $ticket_date
 * @property string|null $ticket_number
 * @property int|null $appointment_id
 * @property int|null $visit_id
 * @property int $patient_id
 * @property TicketStatus|null $status
 * @property \Carbon\Carbon|null $called_at
 * @property \Carbon\Carbon|null $finished_at
 */
class VisitTicket extends Model
{
    use HasFactory, HasStatus;

    protected $fillable = [
        'ticket_date',
        'ticket_number',
        'appointment_id',
        'visit_id',
        'patient_id',
        'status',
        'called_at',
        'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'ticket_date' => 'date',
            'called_at' => 'datetime',
            'finished_at' => 'datetime',
            'status' => TicketStatusCast::class,
        ];
    }

    // ==================== Relationships ====================

    /**
     * The patient this ticket belongs to.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * The appointment associated with this ticket.
     */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    /**
     * The visit associated with this ticket.
     */
    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }
}

