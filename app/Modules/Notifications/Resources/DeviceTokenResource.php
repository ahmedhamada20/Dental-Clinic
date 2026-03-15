<?php

namespace App\Modules\Notifications\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeviceTokenResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'patient_id' => $this->patient_id,
            'device_type' => $this->device_type,
            'firebase_token' => $this->firebase_token,
            'device_name' => $this->device_name,
            'app_version' => $this->app_version,
            'is_active' => (bool) $this->is_active,
            'last_used_at' => optional($this->last_used_at)?->toIso8601String(),
            'created_at' => optional($this->created_at)?->toIso8601String(),
            'updated_at' => optional($this->updated_at)?->toIso8601String(),
        ];
    }
}
