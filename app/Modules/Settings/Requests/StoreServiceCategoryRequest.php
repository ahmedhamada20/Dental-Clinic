<?php

namespace App\Modules\Settings\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreServiceCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'medical_specialty_id' => ['required', 'integer', 'exists:medical_specialties,id'],
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => [
                'required',
                'string',
                'max:255',
                Rule::unique('service_categories', 'name_en')
                    ->where(fn ($query) => $query->where('medical_specialty_id', $this->integer('medical_specialty_id'))),
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
