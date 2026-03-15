<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StorePromotionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title_ar'       => ['required', 'string', 'max:255'],
            'title_en'       => ['nullable', 'string', 'max:255'],
            'code'           => ['nullable', 'string', 'max:50', 'unique:promotions,code'],
            'promotion_type' => ['required', 'string', 'in:invoice_percent,invoice_fixed,service_percent,service_fixed,free_consultation'],
            'value'          => ['nullable', 'numeric', 'min:0'],
            'applies_once'   => ['sometimes', 'boolean'],
            'starts_at'      => ['required', 'date'],
            'ends_at'        => ['required', 'date', 'after_or_equal:starts_at'],
            'is_active'      => ['sometimes', 'boolean'],
            'notes'          => ['nullable', 'string'],
            'service_ids'    => ['nullable', 'array'],
            'service_ids.*'  => ['integer', 'exists:services,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'applies_once' => $this->boolean('applies_once'),
            'is_active'    => $this->boolean('is_active'),
        ]);
    }
}

