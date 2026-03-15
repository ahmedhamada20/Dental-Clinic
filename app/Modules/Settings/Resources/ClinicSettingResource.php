<?php

namespace App\Modules\Settings\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClinicSettingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'clinic_name' => $this['clinic_name'] ?? null,
            'phone' => $this['phone'] ?? null,
            'email' => $this['email'] ?? null,
            'address' => $this['address'] ?? null,
            'timezone' => $this['timezone'] ?? null,
        ];
    }
}
