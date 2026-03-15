<?php

namespace App\Modules\Appointments\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AvailableSlotResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'start_time' => $this['start_time'],
            'end_time' => $this['end_time'],
            'duration_minutes' => $this['duration_minutes'],
            'is_available' => $this['is_available'],
        ];
    }
}

