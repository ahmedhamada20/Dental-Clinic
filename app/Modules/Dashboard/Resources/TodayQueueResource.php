<?php

namespace App\Modules\Dashboard\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TodayQueueResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'appointment_id' => $this['appointment_id'] ?? null,
            'patient_id' => $this['patient_id'] ?? null,
            'patient_name' => $this['patient_name'] ?? null,
            'doctor_id' => $this['doctor_id'] ?? null,
            'doctor_name' => $this['doctor_name'] ?? null,
            'appointment_time' => $this['appointment_time'] ?? null,
            'status' => $this['status'] ?? null,
        ];
    }
}
