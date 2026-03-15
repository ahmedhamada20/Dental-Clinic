<?php

namespace App\Modules\Settings\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkingHourResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this['id'] ?? null,
            'day' => $this['day'] ?? null,
            'start_time' => $this['start_time'] ?? null,
            'end_time' => $this['end_time'] ?? null,
            'is_active' => (bool) ($this['is_active'] ?? true),
        ];
    }
}
