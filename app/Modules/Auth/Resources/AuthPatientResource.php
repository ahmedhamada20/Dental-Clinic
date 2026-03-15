<?php

namespace App\Modules\Auth\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AuthPatientResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'patient_code' => $this->patient_code ?? null,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name ?? trim($this->first_name . ' ' . $this->last_name),
            'phone' => $this->phone,
            'email' => $this->email,
            'gender' => $this->gender ?? null,
            'profile_image' => $this->profile_image ?? null,
            'date_of_birth' => $this->date_of_birth?->format('Y-m-d'),
            'age' => $this->age ?? null,
            'status' => $this->status?->value ?? $this->status,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}

