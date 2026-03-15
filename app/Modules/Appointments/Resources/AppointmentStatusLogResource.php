<?php

namespace App\Modules\Appointments\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentStatusLogResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'appointment_id' => $this->appointment_id,
            'old_status' => $this->old_status,
            'new_status' => $this->new_status,
            'changed_by' => $this->changed_by_type ? [
                'type' => $this->changed_by_type,
                'id' => $this->changed_by_id,
            ] : null,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}

