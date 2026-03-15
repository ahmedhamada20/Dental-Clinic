<?php

namespace App\Modules\Settings\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServiceCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $category = $this->route('serviceCategory') ?? $this->route('service_category');
        $categoryId = $category?->id ?? $this->route('id');
        $medicalSpecialtyId = $this->has('medical_specialty_id')
            ? $this->integer('medical_specialty_id')
            : $category?->medical_specialty_id;

        return [
            'medical_specialty_id' => ['sometimes', 'required', 'integer', 'exists:medical_specialties,id'],
            'name_ar' => ['sometimes', 'required', 'string', 'max:255'],
            'name_en' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('service_categories', 'name_en')
                    ->ignore($categoryId)
                    ->where(fn ($query) => $query->where('medical_specialty_id', $medicalSpecialtyId)),
            ],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'medical_specialty_id.exists' => __('admin.validation.selected_specialty_invalid'),
            'name_en.unique' => __('admin.validation.service_category_unique_per_specialty'),
        ];
    }
}

