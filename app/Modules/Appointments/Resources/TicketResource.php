<?php

namespace App\Modules\Appointments\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'ticket_number' => $this->ticket_number,
            'ticket_date' => $this->ticket_date?->format('Y-m-d'),
            'patient' => [
                'id' => $this->patient->id,
                'full_name' => $this->patient->full_name,
                'phone' => $this->patient->phone,
            ],
            'appointment_id' => $this->appointment_id,
            'visit_id' => $this->visit_id,
            'status' => $this->status?->value,
            'called_at' => $this->called_at?->format('Y-m-d H:i:s'),
            'finished_at' => $this->finished_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}

