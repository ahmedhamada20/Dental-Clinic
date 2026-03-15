<?php

namespace App\Modules\Appointments\Services;

class TicketNumberGeneratorService
{
    /**
     * Generate a unique ticket number for the day.
     * Format: TICKET-YYYYMMDD-XXXX (where XXXX is sequential)
     */
    public function generateTicketNumber(): string
    {
        $datePrefix = now()->format('Ymd');
        $basePrefix = "TICKET-{$datePrefix}";

        // Get the last ticket number for today
        $lastTicket = \App\Models\Appointment\VisitTicket::whereDate('ticket_date', now())
            ->orderByDesc('id')
            ->first();

        $sequence = 1;
        if ($lastTicket && str_starts_with($lastTicket->ticket_number, $basePrefix)) {
            $parts = explode('-', $lastTicket->ticket_number);
            $lastSequence = (int)end($parts);
            $sequence = $lastSequence + 1;
        }

        return $basePrefix . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}

