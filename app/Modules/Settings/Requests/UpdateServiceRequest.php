<?php

namespace App\Modules\Settings\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $serviceId = $this->route('service') ?? $this->route('id');

        return [
            'category_id' => ['sometimes', 'required', 'exists:service_categories,id'],
            'code' => ['sometimes', 'required', 'string', 'max:100', 'unique:services,code,' . $serviceId],
            'name_ar' => ['sometimes', 'required', 'string', 'max:255'],
            'name_en' => ['sometimes', 'required', 'string', 'max:255'],
            'description_ar' => ['nullable', 'string'],
            'description_en' => ['nullable', 'string'],
            'default_price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'duration_minutes' => ['nullable', 'integer', 'min:0'],
            'is_bookable' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.exists' => __('admin.validation.selected_category_invalid'),
            'code.unique' => __('admin.validation.service_code_unique'),
        ];
    }
}

