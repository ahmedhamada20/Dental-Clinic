<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id'      => ['nullable', 'integer', 'exists:service_categories,id'],
            'code'             => ['nullable', 'string', 'max:50', 'unique:services,code'],
            'name_ar'          => ['required', 'string', 'max:255'],
            'name_en'          => ['nullable', 'string', 'max:255'],
            'description_ar'   => ['nullable', 'string'],
            'description_en'   => ['nullable', 'string'],
            'default_price'    => ['required', 'numeric', 'min:0'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
            'is_bookable'      => ['sometimes', 'boolean'],
            'is_active'        => ['sometimes', 'boolean'],
            'sort_order'       => ['nullable', 'integer', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_bookable' => $this->boolean('is_bookable'),
            'is_active'   => $this->boolean('is_active'),
            'sort_order'  => $this->input('sort_order', 0),
        ]);
    }
}

