<?php

namespace App\Modules\Settings\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ServiceCategoryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'medical_specialty_id' => $this->medical_specialty_id,
            'medical_specialty_name' => $this->whenLoaded('medicalSpecialty', fn () => $this->medicalSpecialty?->name),
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
            'services_count' => $this->whenCounted('services'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
