<?php

namespace App\Modules\Settings\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkingDayResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'days' => $this['days'] ?? [],
        ];
    }
}
