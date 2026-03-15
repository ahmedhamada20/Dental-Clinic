<?php

namespace App\Modules\Settings\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'medical_specialty_id' => $this->whenLoaded('category', fn () => $this->category?->medical_specialty_id),
            'medical_specialty_name' => $this->whenLoaded('category', fn () => $this->category?->medicalSpecialty?->name),
            'code' => $this->code,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'description_ar' => $this->description_ar,
            'description_en' => $this->description_en,
            'default_price' => $this->default_price,
            'duration_minutes' => $this->duration_minutes,
            'is_bookable' => $this->is_bookable,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
            'category' => new ServiceCategoryResource($this->whenLoaded('category')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
